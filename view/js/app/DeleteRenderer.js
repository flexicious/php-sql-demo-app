/**
 * Created by 19.06.2013-7pm on 08-Dec-15.
 */
(function(window){

    var DeleteRenderer = function(){
        flexiciousNmsp.UIComponent.apply(this, ["div"]);
        this.domElement.style.textAlign = "center";
        this.domElement.style.display = "inline-block";
        this.domElement.style.width = "100%";

        var button = new flexiciousNmsp.UIComponent("button");
        button.domElement.className = "delete-button";
        button.setInnerHTML("Delete");
        button.addEventListener(this, "click", this.onDeleteClick);

        this.addChild(button);
    };

    DeleteRenderer.prototype = new flexiciousNmsp.UIComponent();
    DeleteRenderer.prototype.typeName = DeleteRenderer.typeName = 'CustomHeaderRender';
    DeleteRenderer.prototype.getClassNames=function(){
        return ["DeleteRenderer","UIComponent"];
    };


    DeleteRenderer.prototype.setData = function(data){
        this.data = data;
        this.setVisible(this.parent.level.getNestDepth() > 1);
    };

    DeleteRenderer.prototype.setText = function(text){

    };

    DeleteRenderer.prototype.onDeleteClick = function (event) {
        if(confirm("Are you sure? do you want delete this item ?")){
            var grid = this.parent.level.grid;
            var dp = grid.getDataProvider();
            for(var i = 0; i < dp.length; i++){
                var data = dp[i];
                for(var j = 0; j < data.children.length; j++){
                    if(data.children.indexOf(this.data)  != -1){
                        data.children.splice(j, 1);
                        break;
                    }
                }
            }
            grid.rebuild();
        }
    };

    window.DeleteRenderer = DeleteRenderer;

}(window));