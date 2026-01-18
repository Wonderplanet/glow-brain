using System.IO;
using System.Linq;
using UnityEditor;
using UnityEngine;
using GLOW.Scenes.InGame.Presentation.Field;

namespace GLOW.Editor.CompositeUnitAsset
{
    public class CompositeUnitAssetEditor : EditorWindow
    {
        string[] _characterDirectoryNamePrefixes = new[]
        {
            "chara_",
            "enemy_",
            "Chara_",
            "Enemy_",
        };

        string[] _targetDirectories;
        int _selectedIndex;

        [MenuItem("GLOW/ユニットアセット構成")]
        static void OpenCompositeUnitAssetEditor()
        {
            GetWindow<CompositeUnitAssetEditor>("CompositeUnitAssetEditor");
        }

        void OnEnable()
        {
            var directoryInfo = new DirectoryInfo(UnitEffectAssetPath.Root);
            var directories = directoryInfo.GetDirectories();
            _targetDirectories = directories
                .Select(directory => directory.Name)
                .Where(IsCharacterDirectory)
                .ToArray();
        }

        string _selectedReleaseKey;
        void OnGUI()
        {
            _selectedReleaseKey = EditorGUILayout.TextField("リリースキー", _selectedReleaseKey);

            _selectedIndex = EditorGUILayout.Popup("対象ファイル選択", _selectedIndex, _targetDirectories);

            var assetKey = _targetDirectories[_selectedIndex];
            GUILayout.Space(20);

            if (GUILayout.Button("TimelineAnimation設定"))
            {
                TimelineAnimationComponentInjector.SetupTimelineAnimation(assetKey);
            }

            if (GUILayout.Button("UnitAttackViewInfoSet生成・エフェクト登録"))
            {
                UnitAttackViewInfoSetGenerator.GenerateUnitAttackViewInfoSet(assetKey, _selectedReleaseKey);
            }

            if (GUILayout.Button("カットイン原画コマをAssetBundleフォルダに移動"))
            {
                UnitCutInKomaMover.MoveUnitCutInKoma(assetKey, _selectedReleaseKey);
            }

            if (GUILayout.Button("キャラマテリアルシェーダー置き換え"))
            {
                ReplaceCharacterMaterialShader(assetKey);
            }

            if (GUILayout.Button("ユニットプレハブ生成・Outline設定"))
            {
                UnitPrefabGenerator.GenerateUnitPrefabWithOutline(assetKey, _selectedReleaseKey);
            }
        }

        void ReplaceCharacterMaterialShader(string assetKey)
        {
            var shaderGuid = "1a951750ced14a08a18a8af46d48313e";
            var shaderPath = AssetDatabase.GUIDToAssetPath(shaderGuid);

            if (string.IsNullOrEmpty(shaderPath))
            {
                Debug.LogError($"Shader with GUID {shaderGuid} not found");
                return;
            }

            var shader = AssetDatabase.LoadAssetAtPath<Shader>(shaderPath);
            if (shader == null)
            {
                Debug.LogError($"Failed to load shader at path: {shaderPath}");
                return;
            }

            var materialPath = $"Assets/GLOW/Graphics/Characters/{assetKey}/Spine/{assetKey}_Material.mat";
            var material = AssetDatabase.LoadAssetAtPath<Material>(materialPath);

            if (material == null)
            {
                Debug.LogError($"Material not found at path: {materialPath}");
                return;
            }

            material.shader = shader;
            EditorUtility.SetDirty(material);
            AssetDatabase.SaveAssets();

            Debug.Log($"Successfully replaced shader for material: {materialPath}");
        }

        bool IsCharacterDirectory(string directoryName)
        {
            return _characterDirectoryNamePrefixes.Any(directoryName.StartsWith);
        }
    }
}
