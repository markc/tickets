<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin/login');
});
