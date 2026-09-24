@extends('layouts.app')

@section('title', 'POS Terminal')

@section('content')
<div class="page-head">
    <div>
        <h1>POS Terminal</h1>
        <p>Point of sale for direct product sales.</p>
    </div>
</div>

<div class="alert alert-info">
    <strong>POS page content</strong>
</div>

<script>
console.log('POS page loaded');
console.log('Document title:', document.title);
console.log('Body children count:', document.body.children.length);
for (let i = 0; i < document.body.children.length; i++) {
    console.log('Body child', i, document.body.children[i].tagName, document.body.children[i].className);
}
</script>