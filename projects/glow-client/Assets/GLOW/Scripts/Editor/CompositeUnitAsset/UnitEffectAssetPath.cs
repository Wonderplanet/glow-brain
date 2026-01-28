using System.Collections.Generic;
using System.IO;

namespace GLOW.Editor.CompositeUnitAsset
{
    public class UnitEffectAssetPath
    {
        // エフェクトフォルダのルートパス
        public static readonly string Root = "Assets/GLOW/Graphics/Characters";

        // Normalエフェクトパス
        public string AtEf => GetEffectPrefabPath(AssetKey, "AtEf", "AtEf");
        public string AtEfMir => GetEffectPrefabPath(AssetKey, "AtEf", "AtEf_mir");
        public string SpEf => GetEffectPrefabPath(AssetKey, "SpEf", "SpEf");
        public string SpEfMir => GetEffectPrefabPath(AssetKey, "SpEf", "SpEf_mir");
        public string SpEfFollow => GetEffectPrefabPath(AssetKey, "SpEf", "SpEf_Follow");
        public string SpEfNotFollow => GetEffectPrefabPath(AssetKey, "SpEf", "SpEf_NotFollow");

        // Specialエフェクトパス
        public string AtOf => GetEffectPrefabPath(AssetKey, "AtOf", "AtOf");
        public string AtOfMir => GetEffectPrefabPath(AssetKey, "AtOf", "AtOf_mir");
        public string SpOf => GetEffectPrefabPath(AssetKey, "SpOf", "SpOf");
        public string SpOfMir => GetEffectPrefabPath(AssetKey, "SpOf", "SpOf_mir");

        // CutInエフェクトパス
        public string CutInSpCutInBackground => string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Prefabs/{0}_SpCutIn_Background.prefab", AssetKey);
        public string CutInSpCutInChaEf => string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Prefabs/{0}_SpCutIn_ChaEF.prefab", AssetKey);
        public string CutInKoma => GetCutInKomaPath(AssetKey);

        // ファイルパスの揺れを補正するためのリスト
        public List<(string prefixVariation, string suffixVariation)> EffectPathVariations = new()
        {
            ("", ""),
            ("", "_"),
            ("_", ""),
            ("_", "_"),
        };

        public string AssetKey { get; }

        public UnitEffectAssetPath(string assetKey)
        {
            AssetKey = assetKey;
        }

        string GetEffectPrefabPath(string assetKey, string pathSuffix, string fileSuffix)
        {
            foreach(var variation in EffectPathVariations)
            {
                var path = CreateEffectPath(assetKey, variation.prefixVariation + pathSuffix, variation.suffixVariation + fileSuffix);
                if (File.Exists(path))
                {
                    return path;
                }
            }

            return string.Empty;
        }

        string CreateEffectPath(string assetKey, string pathSuffix, string fileSuffix)
        {
            var subPath = string.Format("/{0}/{0}{1}/Prefabs/{0}{2}.prefab", assetKey, pathSuffix, fileSuffix);
            return Root + subPath;
        }
        
        string GetCutInKomaPath(string assetKey)
        {
            var underscoreIndex = assetKey.IndexOf('_');
            var assetKey2 = assetKey.Substring(underscoreIndex+1);   // assetKeyの先頭についてるenemy_やchara_を除く
            
            var pathVariations = new[]
            {
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Textures/{1}_SpCutIn04C.png", assetKey, assetKey),
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Textures/{1}_SpCutIn04C.png", assetKey, assetKey2),
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Texture/{1}_SpCutIn04C.png", assetKey, assetKey),
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Texture/{1}_SpCutIn04C.png", assetKey, assetKey2),
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Textures/{1}_SpCutIn04.png", assetKey, assetKey),
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Textures/{1}_SpCutIn04.png", assetKey, assetKey2),
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Texture/{1}_SpCutIn04.png", assetKey, assetKey),
                string.Format("Assets/GLOW/Graphics/Characters/{0}/CutIn/Texture/{1}_SpCutIn04.png", assetKey, assetKey2),
            };
            
            foreach (var path in pathVariations)
            {
                if (File.Exists(path))
                {
                    return path;
                }
            }

            return "";
        }
    }
}
