{extends file="index.tpl"}
{block name="content"}
<div class="error-page">
  <h2 class="headline text-info"> 404</h2>
  <div class="error-content">
    <h3><i class="glyphicon glyphicon-warning-sign text-yellow"></i>Event <b>'{$sEvent}'</b> not defined</h3>
    <p>
      We could not find the page you were looking for. 
      Meanwhile, you may <a href='/admin/'>return to dashboard</a> or try using the search form.
    </p>
    <form class='search-form'>
      <div class='form-group'>
        <input type="text" name="search" class='form-control' placeholder="Search"/>
        <div class="form-group-btn">
          <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
        </div>
      </div>
      <!-- /.form-group -->
    </form>
  </div>
  <!-- /.error-content -->
</div>
<!-- /.error-page -->
{/block}