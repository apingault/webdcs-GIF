
<script>
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>

<div class="content">
<iframe style="width: 100%;" src="includes/SYSINFO/" frameborder="0" scrolling="no" onload="resizeIframe(this)" />
</div>