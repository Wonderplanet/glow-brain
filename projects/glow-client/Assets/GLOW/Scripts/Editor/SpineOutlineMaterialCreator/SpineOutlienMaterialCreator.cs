using System.IO;
using System.Linq;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.SpineOutlineMaterialCreator
{
    public class SpineOutlienMaterialCreator : MonoBehaviour
    {
        const string OutlineShaderName = "GLOW/InGame/SpineOutlineWithUnitColor";
        static readonly int OutlineReferenceTexWidthPropertyId = Shader.PropertyToID("_OutlineReferenceTexWidth");
        static readonly int OutlineReferenceOutlineWidthPropertyId = Shader.PropertyToID("_OutlineWidth");
        static readonly float OutlineReferenceOutlineWidthPropertyValue = 2.5f;
        static readonly int OutlineReferenceThresholdEndPropertyId = Shader.PropertyToID("_ThresholdEnd");
        static readonly float OutlineReferenceThresholdEndPropertyValue = 0f;
        static readonly int OutlineReferenceColorPropertyId = Shader.PropertyToID("_OutlineColor");
        static readonly Color32 OutlineReferenceColorPropertyValue = new Color32(34, 34, 34, 255);

        static readonly string OutlineMaterialFormat = "{0}_MaterialOutLine";

        [MenuItem("Assets/Create/GLOW/Spine/Create OutlineMaterial from SpineMaterial")]
        static void CreateMaterialFromTexture()
        {
            var selectedMaterials = Selection.GetFiltered(typeof(Material), SelectionMode.Assets).Cast<Material>()
                .ToList();
            // Skip execution if there's no texture selected
            if (selectedMaterials.Count == 0) return;

            foreach (var material in selectedMaterials)
            {
                var materialName = GetOutlineMaterialName(material);
                var path = Path.GetDirectoryName(AssetDatabase.GetAssetPath(material));

                var outlineMaterial = CreateMaterial(materialName, path);

                SetOutlineMaterialProperties(material, outlineMaterial);
            }

            // Save the changes to the material
            AssetDatabase.SaveAssets();
            AssetDatabase.Refresh();
        }

        static void SetOutlineMaterialProperties(Material baseMaterial, Material outlineMaterial)
        {
            outlineMaterial.mainTexture = baseMaterial.mainTexture;
            outlineMaterial.SetFloat(OutlineReferenceTexWidthPropertyId, GetTexLength(baseMaterial.mainTexture));
            outlineMaterial.SetFloat(OutlineReferenceOutlineWidthPropertyId, OutlineReferenceOutlineWidthPropertyValue);
            outlineMaterial.SetFloat(OutlineReferenceThresholdEndPropertyId, OutlineReferenceThresholdEndPropertyValue);
            outlineMaterial.SetColor(OutlineReferenceColorPropertyId, OutlineReferenceColorPropertyValue);
        }

        static string GetOutlineMaterialName(Material baseMaterial)
        {
            //ex: dan_00101_Material > dan_00101_MaterialOutLine
            var editBaseMaterialName = baseMaterial.name.Replace("_Material", "");
            return string.Format(OutlineMaterialFormat, editBaseMaterialName);
        }

        static float GetTexLength(Texture texture)
        {
           return texture != null ? texture.width : 1024f;
        }

        static Material CreateMaterial(string materialName, string assetPath)
        {
            var newMaterial = new Material(Shader.Find(OutlineShaderName));
            var format = "{0}/{1}";
            AssetDatabase.CreateAsset(newMaterial, string.Format(format, assetPath, $"{materialName}.mat"));
            return newMaterial;
        }
    }
}
