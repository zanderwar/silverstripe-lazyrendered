<?php

class LazyRendered extends LeftAndMain
{

    protected $active_page;

    /**
     * Template tag to fetch the body template for the required action
     *
     * @return \HTMLText
     */
    public function getBody()
    {
        $urlParams = $this->getURLParams();

        if (isset($urlParams[ 'Action' ])) {
            $render = $urlParams[ 'Action' ] . 'Render';

            return $this->{$render}($urlParams[ 'ID' ], $urlParams[ 'OtherID' ])['Body'];
        }

        if ($this->hasMethod($method = 'indexRender')) {
            $response = $this->{$method}($urlParams[ 'ID' ], $urlParams[ 'OtherID' ]);
            $this->validateActionResponse($method, $response);

            return $response[ 'Body' ];
        }

        $error = _t(
            'LazyRendered.NoBodyOnIndex',
            get_called_class() . '::indexRender() should be returning array with at least a "Body" key'
        );

        user_error(
            $error
        );

        return $error;
    }

    /**
     * Handle action override to set the title etc
     *
     * @param $request
     * @param $action
     *
     * @return \HTMLText|\SS_HTTPResponse|string
     */
    public function handleAction($request, $action)
    {
        $allowed_actions = $this->allowedActions();

        // We can't alter index in LeftAndMain so we just let the parent do the work, and rely
        // on $this->getBody() & $Body template tag
        if (
            $action == 'index' ||
            (!is_array($allowed_actions) || !in_array($action, $allowed_actions)) ||
            $this->isWhitelisted($action)
        ) {
            return parent::handleAction($request, $action);
        }

        $method = $action . 'Render';

        if ($this->hasMethod($method)) {
            $response = $this->{$method}();

            $this->validateActionResponse($method, $response);

            $this->active_page = $response['Title'] ?: ucwords($action);

            return $this->render(
                $response
            );
        }

        if (Director::get_environment_type() == 'dev') {
            user_error(_t(
                'LazyRendered.ActionMissing',
                'You render method "{action}Render()" is missing in ' . get_called_class() .
                ' did you mean to whitelist the action "{action}" so that Lazy Rendered does not attempt to handle it?',
                "The message shown when a render method is missing, but is an allowed action",
                array(
                    'action' => $action
                )
            ));
            die();
        }

        // shouldn't get here, action is missing or action is not whitelisted redirect to module index
        return $this->redirect($this->Link());
    }

    /**
     * Actions that are whitelist won't be passed through the Lazy Rendered module
     *
     * @param $action
     *
     * @return bool
     */
    public function isWhitelisted($action)
    {
        if (!$whitelist = $this->getWhitelist()) {
            return false;
        }

        return (is_array($whitelist) && in_array($action, $whitelist)) ? true : false;
    }

    /**
     * Fetches the whitelist
     *
     * @return bool|mixed
     */
    public function getWhitelist()
    {
        return (isset($this->whitelist)) ? $this->whitelist : false;
    }

    /**
     * Validates the actionRender() response
     *
     * @param      $action
     * @param null $response
     *
     * @return void
     */
    private function validateActionResponse($action, $response = null)
    {
        if (!is_array($response)) {
            user_error(
                _t(
                    'LazyRendered.ActionMustReturnArray',
                    'Lazy Rendered actions must return an array: {action}',
                    'The error message that appears when a lazyRender() action does not return an array',
                    array(
                        'action' => $action
                    )
                )
            );
        } else if (!array_key_exists('Body', $response)) {
            user_error(
                _t(
                    'LazyRendered.ActionArrayMissingBody',
                    'Lazy Rendered actions must return an array with a "Body" key: {action}',
                    'The error message that appears when a lazyRender() method returns an array but not have a "Body" key',
                    array(
                        'action' => $action
                    )
                )
            );
        }
    }

    /**
     * Overrides Breadcrumbs for our own
     *
     * @param bool $unlinked
     */
    public function Breadcrumbs($unlinked = false)
    {
        $items = ArrayList::create();

        $items->push(
            array(
                'Title' => $this->curr()->config()->get('menu_title'),
                'Link'  => $this->Link(),
            )
        );

        if ($this->active_page) {
            $items->push(
                array(
                    'Title' => $this->active_page,
                    'Link'  => false,
                )
            );
        }


        return $items;
    }

}