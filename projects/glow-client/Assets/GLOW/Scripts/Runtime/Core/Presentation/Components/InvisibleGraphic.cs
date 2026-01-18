using UnityEditor;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(CanvasRenderer))]
    public class InvisibleGraphic : Graphic
    {
        protected override void OnPopulateMesh(VertexHelper vh)
        {
            base.OnPopulateMesh(vh);
            vh.Clear();
        }
    }

#if UNITY_EDITOR
    [CustomEditor(typeof(InvisibleGraphic))]
    class GraphicCastEditor : Editor
    {
        public override void OnInspectorGUI()
        {

        }
    }
#endif
}
