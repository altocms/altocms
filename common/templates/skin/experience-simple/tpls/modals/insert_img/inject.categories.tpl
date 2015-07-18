{if E::IsUser()}
    <div class="menu-sub">Загрузить изображение:</div>
    <div class="js-image-categories-tree ">
        <a class="btn btn-default" data-category="insert-from-pc" href="#">{$aLang.insertimg_from_pc}</a>
        <a class="btn btn-default" data-category="insert-from-link" href="#">{$aLang.insertimg_from_link}</a>
    </div>
    <br/>
    <div class="menu-sub">Уже загруженные:</div>
    {menu id='image_insert' class='js-image-categories-tree image-categories-tree nav nav-pills nav-stacked'}
{/if}
