using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Reflection;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.CompositeUnitAsset
{
    public class UnitAttackViewInfoSetGenerator : UnityEditor.Editor
    {
        public static void GenerateUnitAttackViewInfoSet(string assetKey, string releaseKey)
        {
            var assetPath = new UnitEffectAssetPath(assetKey);

            var path = GetUnitAttackViewInfoSetPath(assetPath.AssetKey, releaseKey);
            var normalAttackEffectPaths = GetNormalEffectPaths(assetPath);
            var specialAttackEffectPaths = GetSpecialEffectPaths(assetPath);

            GenerateUnitAttackViewInfoSet(path, normalAttackEffectPaths, specialAttackEffectPaths);
        }

        static void GenerateUnitAttackViewInfoSet(
            string path,
            List<(string propertyName, string effectPath)> normalAttackEffectPaths,
            List<(string propertyName, string effectPath)> specialAttackEffectPaths)
        {
            var isExistFile = File.Exists(path);

            var attackView = isExistFile
                ? AssetDatabase.LoadAssetAtPath<UnitAttackViewInfoSet>(path)
                : CreateInstance<UnitAttackViewInfoSet>();

            attackView.NormalAttackViewInfo = CreateUnitAttackViewInfo(normalAttackEffectPaths);
            attackView.SpecialAttackViewInfo = CreateUnitAttackViewInfo(specialAttackEffectPaths);

            if (!isExistFile)
            {
                AssetDatabase.CreateAsset(attackView, path);
            }
            AssetDatabase.SaveAssets();
            AssetDatabase.Refresh();

            DebugLog(path, normalAttackEffectPaths, specialAttackEffectPaths);
        }

        static UnitAttackViewInfo CreateUnitAttackViewInfo(List<(string propertyName, string effectPath)> effectPaths)
        {
            var info = new UnitAttackViewInfo();
            var type = info.GetType();

            foreach (var (propertyName, effectPath) in effectPaths)
            {
                var field = type.GetField(propertyName, BindingFlags.NonPublic | BindingFlags.Instance);
                field.SetValue(info, AssetDatabase.LoadAssetAtPath<GameObject>(effectPath));
            }

            return info;
        }

        static void DebugLog(
            string path,
            List<(string propertyName, string effectPath)> normalAttackEffectPaths,
            List<(string propertyName, string effectPath)> specialAttackEffectPaths)
        {
            var stringBuilder = new System.Text.StringBuilder();

            foreach (var (propertyName, effectPath) in normalAttackEffectPaths)
            {
                stringBuilder.AppendLine("  NormalAttack");
                stringBuilder.AppendLine($"    {propertyName} - {effectPath}");
            }

            foreach (var (propertyName, effectPath) in specialAttackEffectPaths)
            {
                stringBuilder.AppendLine("  SpecialAttack");
                stringBuilder.AppendLine($"    {propertyName} - {effectPath}");
            }

            Debug.Log($"[CompositeUnitAssetEditor] GenerateUnitAttackViewInfo:{path}\n{stringBuilder}");
        }

        static string GetUnitAttackViewInfoSetPath(string assetKey, string releaseKey)
        {
            var folderPath = $"Assets/GLOW/AssetBundles/unit_attack_view_info_set/unit_attack_view_info_set!{releaseKey}";
            var fileName = $"unit_attack_view_info_set_{assetKey.ToLowerInvariant()}.asset";
            return Path.Combine(folderPath, fileName);
        }

        static List<(string propertyName, string effectPath)> GetNormalEffectPaths(UnitEffectAssetPath assetPath)
        {
            var list = new List<(string propertyName, string effectPath)>()
            {
                ("_attackEffect", assetPath.AtEf),
                ("_attackEffectMirror", assetPath.AtEfMir),
                ("_attackMangaEffect", assetPath.AtOf),
                ("_attackMangaEffectMirror", assetPath.AtOfMir),
            };

            return list
                .Where(param => File.Exists(param.effectPath))
                .ToList();
        }

        static List<(string propertyName, string effectPath)> GetSpecialEffectPaths(UnitEffectAssetPath assetPath)
        {
            var list = new List<(string propertyName, string effectPath)>()
            {
                ("_attackEffect", assetPath.SpEf),
                ("_attackEffectMirror", assetPath.SpEfMir),
                ("_attackLastingEffect", assetPath.SpEfFollow),
                ("_attackStayedLastingEffect", assetPath.SpEfNotFollow),
                ("_attackMangaEffect", assetPath.SpOf),
                ("_attackMangaEffectMirror", assetPath.SpOfMir),
                ("_cutInPrefab_background", assetPath.CutInSpCutInBackground),
                ("_cutInPrefab_unitEffect", assetPath.CutInSpCutInChaEf),
            };

            return list
                .Where(param => File.Exists(param.effectPath))
                .ToList();
        }
    }
}
