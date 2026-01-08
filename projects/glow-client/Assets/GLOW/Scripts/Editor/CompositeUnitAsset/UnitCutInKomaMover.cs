
using System.IO;
using UnityEditor;

namespace GLOW.Editor.CompositeUnitAsset
{
    public class UnitCutInKomaMover : UnityEditor.Editor
    {
        public static void MoveUnitCutInKoma(string assetKey, string releaseKey)
        {
            var assetPath = new UnitEffectAssetPath(assetKey);
            var cutInKomaPath = assetPath.CutInKoma;
            if (string.IsNullOrEmpty(cutInKomaPath)) return;

            var folderPath = $"Assets/GLOW/AssetBundles/unit_cutin_koma/unit_cutin_koma!{releaseKey}";
            var filename = $"unit_cutin_koma_{assetKey.ToLowerInvariant()}.png";
            var destinationPath = Path.Combine(folderPath, filename);

            AssetDatabase.MoveAsset(cutInKomaPath, destinationPath);
            AssetDatabase.Refresh();
        }
    }
}
