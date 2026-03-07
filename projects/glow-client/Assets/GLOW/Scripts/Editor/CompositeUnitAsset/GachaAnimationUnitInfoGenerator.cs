using System.Collections.Generic;
using System.IO;
using System.Linq;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.CompositeUnitAsset
{
    public class GachaAnimationUnitInfoGenerator : UnityEditor.Editor
    {
        static readonly string FukidashiRoot = "Assets/GLOW/Graphics/Textures/GachaAnimationFukidashi";

        public static void GenerateOrUpdate(string assetKey, string releaseKey)
        {
            if (string.IsNullOrEmpty(releaseKey))
            {
                Debug.LogError("リリースキーが設定されていません");
                return;
            }

            var assetPath = GetGachaAnimationUnitInfoPath(assetKey, releaseKey);
            var folderPath = Path.GetDirectoryName(assetPath);

            if (!string.IsNullOrEmpty(folderPath) && !Directory.Exists(folderPath))
            {
                Directory.CreateDirectory(folderPath);
                AssetDatabase.Refresh();
            }

            var isExistFile = File.Exists(assetPath);

            var unitInfo = isExistFile
                ? AssetDatabase.LoadAssetAtPath<GachaAnimationUnitInfo>(assetPath)
                : CreateInstance<GachaAnimationUnitInfo>();

            SetPictureSprite(unitInfo, assetKey);
            SetFukidashiSettings(unitInfo, assetKey);
            SetTechNameSprite(unitInfo, assetKey);

            if (!isExistFile)
            {
                AssetDatabase.CreateAsset(unitInfo, assetPath);
            }

            EditorUtility.SetDirty(unitInfo);
            AssetDatabase.SaveAssets();
            AssetDatabase.Refresh();

            Debug.Log($"[GachaAnimationUnitInfoGenerator] Generated/Updated: {assetPath}");
        }

        public static void SetPictureSprite(GachaAnimationUnitInfo unitInfo, string assetKey)
        {
            var spritePath = GetPictureSpritePath(assetKey);

            if (string.IsNullOrEmpty(spritePath))
            {
                Debug.LogWarning($"[GachaAnimationUnitInfoGenerator] PictureSprite not found for: {assetKey}");
                return;
            }

            var sprite = AssetDatabase.LoadAssetAtPath<Sprite>(spritePath);

            if (sprite != null)
            {
                unitInfo.PictureSprite = sprite;
                Debug.Log($"[GachaAnimationUnitInfoGenerator] Set PictureSprite: {spritePath}");
            }
        }

        public static void SetFukidashiSettings(GachaAnimationUnitInfo unitInfo, string assetKey)
        {
            var fukidashiSpritePath1 = GetFukidashiSpritePath(assetKey, 1);
            var fukidashiSpritePath2 = GetFukidashiSpritePath(assetKey, 2);

            unitInfo.FukidashiSetting1 ??= new GachaAnimationUnitInfo.GachaFukidashiSetting();
            unitInfo.FukidashiSetting2 ??= new GachaAnimationUnitInfo.GachaFukidashiSetting();

            if (!string.IsNullOrEmpty(fukidashiSpritePath1))
            {
                var sprite = AssetDatabase.LoadAssetAtPath<Sprite>(fukidashiSpritePath1);

                if (sprite != null)
                {
                    unitInfo.FukidashiSetting1.FukidashiSprite = sprite;
                    Debug.Log($"[GachaAnimationUnitInfoGenerator] Set FukidashiSetting1: {fukidashiSpritePath1}");
                }
            }

            if (!string.IsNullOrEmpty(fukidashiSpritePath2))
            {
                var sprite = AssetDatabase.LoadAssetAtPath<Sprite>(fukidashiSpritePath2);

                if (sprite != null)
                {
                    unitInfo.FukidashiSetting2.FukidashiSprite = sprite;
                    Debug.Log($"[GachaAnimationUnitInfoGenerator] Set FukidashiSetting2: {fukidashiSpritePath2}");
                }
            }
        }

        public static void SetTechNameSprite(GachaAnimationUnitInfo unitInfo, string assetKey)
        {
            var spritePath = GetTechNameSpritePath(assetKey);

            if (string.IsNullOrEmpty(spritePath))
            {
                Debug.LogWarning($"[GachaAnimationUnitInfoGenerator] TechNameSprite not found for: {assetKey}");
                return;
            }

            var sprite = AssetDatabase.LoadAssetAtPath<Sprite>(spritePath);

            if (sprite != null)
            {
                unitInfo.TechNameSprite = sprite;
                Debug.Log($"[GachaAnimationUnitInfoGenerator] Set TechNameSprite: {spritePath}");
            }
        }

        public static string GetPictureSpritePath(string assetKey)
        {
            var fileName = $"unit_cutin_koma_{assetKey.ToLowerInvariant()}.png";
            var rootPath = "Assets/GLOW/AssetBundles/unit_cutin_koma";

            // まずリリースキーなしのフォルダを検索
            var basePath = $"{rootPath}/unit_cutin_koma/{fileName}";

            if (File.Exists(basePath))
            {
                return basePath;
            }

            // リリースキー付きのフォルダを検索
            if (Directory.Exists(rootPath))
            {
                foreach (var dir in Directory.GetDirectories(rootPath))
                {
                    var path = Path.Combine(dir, fileName);

                    if (File.Exists(path))
                    {
                        return path;
                    }
                }
            }

            return string.Empty;
        }

        public static string GetFukidashiSpritePath(string assetKey, int index)
        {
            // 2つある場合は _1, _2 が付く
            // 1つの場合はサフィックスなし
            var pathWithIndex = $"{FukidashiRoot}/gacha_animation_fukidashi_{assetKey}_{index}.png";

            if (File.Exists(pathWithIndex))
            {
                return pathWithIndex;
            }

            // index=1の場合のみ、サフィックスなしのパスも探す
            if (index == 1)
            {
                var pathWithoutIndex = $"{FukidashiRoot}/gacha_animation_fukidashi_{assetKey}.png";

                if (File.Exists(pathWithoutIndex))
                {
                    return pathWithoutIndex;
                }
            }

            return string.Empty;
        }

        public static string GetTechNameSpritePath(string assetKey)
        {
            var pathVariations = new[]
            {
                $"Assets/GLOW/Graphics/Characters/{assetKey}/CutIn/Textures/{assetKey}_SpCutIn_TechName.png",
                $"Assets/GLOW/Graphics/Characters/{assetKey}/CutIn/Texture/{assetKey}_SpCutIn_TechName.png",
            };

            foreach (var path in pathVariations)
            {
                if (File.Exists(path))
                {
                    return path;
                }
            }

            return string.Empty;
        }

        /// <summary>
        /// TechNameSpriteのパスを取得する（フォールバック付き）
        /// 指定パスで見つからない場合、フォルダ内の画像を検索する
        /// </summary>
        /// <param name="assetKey">アセットキー</param>
        /// <param name="fallbackImages">フォールバック時に複数画像が見つかった場合のリスト（出力用）</param>
        /// <returns>見つかった画像パス、または空文字</returns>
        public static string GetTechNameSpritePathWithFallback(string assetKey, out List<string> fallbackImages)
        {
            fallbackImages = null;

            // まず通常のパスを検索
            var path = GetTechNameSpritePath(assetKey);

            if (!string.IsNullOrEmpty(path))
            {
                return path;
            }

            // 見つからない場合、フォルダ内の画像を検索
            var folderVariations = new[]
            {
                $"Assets/GLOW/Graphics/Characters/{assetKey}/CutIn/Textures",
                $"Assets/GLOW/Graphics/Characters/{assetKey}/CutIn/Texture",
            };

            foreach (var folder in folderVariations)
            {
                if (!Directory.Exists(folder))
                {
                    continue;
                }

                var pngFiles = Directory.GetFiles(folder, "*.png")
                    .Where(f => f.Contains("TechName"))
                    .ToList();

                if (pngFiles.Count == 1)
                {
                    return pngFiles[0];
                }

                if (pngFiles.Count > 1)
                {
                    fallbackImages = pngFiles;
                    return string.Empty;
                }
            }

            return string.Empty;
        }

        static string GetGachaAnimationUnitInfoPath(string assetKey, string releaseKey)
        {
            var folderPath = $"Assets/GLOW/AssetBundles/gacha_animation/gacha_animation!{releaseKey}";
            var fileName = $"gacha_animation_unit_info_{assetKey.ToLowerInvariant()}.asset";
            return Path.Combine(folderPath, fileName);
        }
    }
}

