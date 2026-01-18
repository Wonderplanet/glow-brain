using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.DebugGrid.Presentation.Views
{
    public class DebugGridView : UIView
    {
        static readonly int GridTilingPropertyId = Shader.PropertyToID("_gridTiling");
        static readonly int GridLineWeightPropertyId = Shader.PropertyToID("_gridLineWeight");

        [SerializeField] Image _gridImage;

        protected override void Start()
        {
            base.Start();
            var gridMaterial = _gridImage.material;
            gridMaterial.SetVector(GridTilingPropertyId, new Vector4(16,10));
            gridMaterial.SetVector(GridLineWeightPropertyId, new Vector4(0.98f,0.98f));
        }
    }
}
