using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using UnityEditor;
using UnityEditor.AddressableAssets;
using UnityEditor.AddressableAssets.Settings;
using UnityEngine;
using WPFramework.Modules.Log;

namespace GLOW.Editor.AddressableFolderEditor
{
    public class MotionAssetAddressableFolderRelocator : EditorWindow
    {
        // Addressable Groupの名前テンプレート（パスカルケース + ハイフン）
        static readonly string[] AddressableGroupNameTemplates = new[]
        {
            "Gacha-Animation!{0}",
            "Unit-Attack-View-Info-Set!{0}",
            "Unit-Cutin-Koma!{0}",
            "Unit-Sd-Prefab!{0}",
        };
        
        // 実際のフォルダ名テンプレート（小文字スネークケース）
        // 親フォルダ/サブフォルダ の構造
        static readonly string[] FolderPathTemplates = new[]
        {
            "gacha_animation/gacha_animation!{0}",
            "unit_attack_view_info_set/unit_attack_view_info_set!{0}",
            "unit_cutin_koma/unit_cutin_koma!{0}",
            "unit_sd_prefab/unit_sd_prefab!{0}",
        };
        
        // 各対象フォルダテンプレートから検索
        // 親フォルダの配列
        static readonly string[] TargetParentFolders = new[]
        {
            "gacha_animation",
            "unit_attack_view_info_set",
            "unit_cutin_koma",
            "unit_sd_prefab",
        };
        
        string[] _characterDirectoryNamePrefixes = new[]
        {
            "chara_",
            "enemy_",
            "Chara_",
            "Enemy_",
        };
        
        string[] _availableDirectories;
        List<string> _selectedDirectories = new List<string>();
        Vector2 _scrollPosition;
        
        string _selectedReleaseKeyBefore;
        string _selectedReleaseKeyAfter;
        
        [MenuItem("GLOW/Addressable/モーション関連アセット フォルダ移動ツール")]
        static void OpenAddressableRelocator()
        {
            GetWindow<MotionAssetAddressableFolderRelocator>("MotionAssetAddressableFolderRelocator");
        }
        
        void OnEnable()
        {
            // AssetBundlesフォルダから対象フォルダを検索してIDを抽出
            const string assetBundlesBasePath = "Assets/GLOW/AssetBundles";
            var idSet = new HashSet<string>();
            
            if (!Directory.Exists(assetBundlesBasePath))
            {
                ApplicationLog.LogWarning(
                    nameof(MotionAssetAddressableFolderRelocator),
                    ZString.Format("AssetBundlesフォルダが見つかりません: {0}", assetBundlesBasePath));
                _availableDirectories = System.Array.Empty<string>();
                return;
            }
            
            foreach (var parentFolder in TargetParentFolders)
            {
                var parentFolderPath = Path.Combine(assetBundlesBasePath, parentFolder);
                
                if (!Directory.Exists(parentFolderPath))
                {
                    continue;
                }
                
                // 親フォルダ内のサブフォルダ（リリースキー付き）を検索
                var subFolders = Directory.GetDirectories(parentFolderPath, parentFolder + "!*");
                
                foreach (var folder in subFolders)
                {
                    // フォルダ内のファイルからIDを抽出
                    var files = Directory.GetFiles(folder, "*.*", SearchOption.TopDirectoryOnly)
                        .Where(f => !f.EndsWith(".meta"))
                        .ToArray();
                    
                    foreach (var file in files)
                    {
                        var fileName = Path.GetFileNameWithoutExtension(file);
                        
                        // ファイル名からIDを抽出（chara_xxx_xxxxx または enemy_xxx_xxxxx）
                        foreach (var prefix in _characterDirectoryNamePrefixes)
                        {
                            var index = fileName.IndexOf(prefix, System.StringComparison.OrdinalIgnoreCase);
                            if (index >= 0)
                            {
                                var idPart = fileName.Substring(index);
                                // ID部分を抽出（chara_abc_12345 形式）
                                var parts = idPart.Split('_');
                                if (parts.Length >= 3)
                                {
                                    var id = ZString.Format("{0}_{1}_{2}", parts[0], parts[1], parts[2]);
                                    idSet.Add(id);
                                }
                                break;
                            }
                        }
                    }
                }
            }
            
            _availableDirectories = idSet.OrderBy(id => id).ToArray();
            
            ApplicationLog.Log(
                nameof(MotionAssetAddressableFolderRelocator),
                ZString.Format("利用可能なID数: {0}", _availableDirectories.Length));
        }
        
        void OnGUI()
        {
            EditorGUILayout.LabelField("Addressable フォルダ移動ツール", EditorStyles.boldLabel);
            EditorGUILayout.Space();
            
            // リリースキーの入力欄
            _selectedReleaseKeyBefore = EditorGUILayout.TextField("リリースキー(移動前)", _selectedReleaseKeyBefore);
            _selectedReleaseKeyAfter = EditorGUILayout.TextField("リリースキー(移動先)", _selectedReleaseKeyAfter);
            
            EditorGUILayout.Space();
            EditorGUILayout.LabelField("移動するキャラクター/敵ID", EditorStyles.boldLabel);
            
            // ID追加ボタン
            EditorGUILayout.BeginHorizontal();
            if (GUILayout.Button("IDを追加", GUILayout.Width(100)))
            {
                ShowAddDirectoryMenu();
            }
            
            if (_selectedDirectories.Count > 0 && GUILayout.Button("全て削除", GUILayout.Width(100)))
            {
                if (EditorUtility.DisplayDialog("確認", "選択中のIDを全て削除しますか？", "はい", "いいえ"))
                {
                    _selectedDirectories.Clear();
                }
            }
            EditorGUILayout.EndHorizontal();
            
            EditorGUILayout.Space();
            
            // 選択済みIDのリスト表示
            _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition, GUILayout.Height(300));
            
            if (_selectedDirectories.Count == 0)
            {
                EditorGUILayout.LabelField("IDを追加してください", EditorStyles.centeredGreyMiniLabel);
            }
            else
            {
                for (var i = _selectedDirectories.Count - 1; i >= 0; i--)
                {
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField(_selectedDirectories[i]);
                    
                    if (GUILayout.Button("削除", GUILayout.Width(50)))
                    {
                        _selectedDirectories.RemoveAt(i);
                    }
                    EditorGUILayout.EndHorizontal();
                }
            }
            
            EditorGUILayout.EndScrollView();
            
            EditorGUILayout.Space();
            
            // 選択数の表示
            EditorGUILayout.LabelField(ZString.Format("選択中: {0}個", _selectedDirectories.Count));
            
            EditorGUILayout.Space();
            
            // 実行ボタン
            GUI.enabled = ValidateInput();
            if (GUILayout.Button("実行", GUILayout.Height(30)))
            {
                ExecuteRelocation();
            }
            GUI.enabled = true;
        }
        
        void ShowAddDirectoryMenu()
        {
            var menu = new GenericMenu();
            
            // 未選択のディレクトリのみメニューに追加
            var unselectedDirectories = _availableDirectories
                .Where(dir => !_selectedDirectories.Contains(dir))
                .ToArray();
            
            if (unselectedDirectories.Length == 0)
            {
                menu.AddDisabledItem(new GUIContent("すべてのIDが選択済みです"));
            }
            else
            {
                foreach (var directory in unselectedDirectories)
                {
                    menu.AddItem(
                        new GUIContent(directory),
                        false,
                        () => { _selectedDirectories.Add(directory); }
                    );
                }
            }
            
            menu.ShowAsContext();
        }
        
        bool ValidateInput()
        {
            // リリースキーが9桁の数字か確認
            if (string.IsNullOrEmpty(_selectedReleaseKeyBefore) || _selectedReleaseKeyBefore.Length != 9)
            {
                return false;
            }
            
            if (string.IsNullOrEmpty(_selectedReleaseKeyAfter) || _selectedReleaseKeyAfter.Length != 9)
            {
                return false;
            }
            
            // 少なくとも1つのディレクトリが選択されているか確認
            if (_selectedDirectories.Count == 0)
            {
                return false;
            }
            
            return true;
        }
        
        void ExecuteRelocation()
        {
            if (!EditorUtility.DisplayDialog(
                "確認",
                ZString.Format(
                    "以下の操作を実行します:\n\n" +
                    "・移動前キー: {0}\n" +
                    "・移動先キー: {1}\n" +
                    "・対象ID数: {2}個\n\n" +
                    "実行しますか？",
                    _selectedReleaseKeyBefore,
                    _selectedReleaseKeyAfter,
                    _selectedDirectories.Count),
                "実行",
                "キャンセル"))
            {
                return;
            }

            try
            {
                var movedFilesCount = 0;
                var settings = AddressableAssetSettingsDefaultObject.Settings;
                
                if (settings == null)
                {
                    EditorUtility.DisplayDialog("エラー", "AddressableAssetSettingsが見つかりません。", "OK");
                    return;
                }

                AssetDatabase.StartAssetEditing();
                
                try
                {
                    // 各対象フォルダテンプレートについて処理
                    for (var i = 0; i < FolderPathTemplates.Length; i++)
                    {
                        var folderPathTemplate = FolderPathTemplates[i];
                        var groupNameTemplate = AddressableGroupNameTemplates[i];
                        
                        var sourceFolderPath = ZString.Format(folderPathTemplate, _selectedReleaseKeyBefore);
                        var destinationFolderPath = ZString.Format(folderPathTemplate, _selectedReleaseKeyAfter);
                        var destinationGroupName = ZString.Format(groupNameTemplate, _selectedReleaseKeyAfter);
                        
                        var result = MoveFilesInFolder(
                            sourceFolderPath,
                            destinationFolderPath,
                            destinationGroupName,
                            _selectedDirectories,
                            settings);
                        
                        movedFilesCount += result;
                    }
                }
                finally
                {
                    AssetDatabase.StopAssetEditing();
                }

                AssetDatabase.SaveAssets();
                AssetDatabase.Refresh();
                
                EditorUtility.DisplayDialog(
                    "完了",
                    ZString.Format("ファイル移動が完了しました。\n移動ファイル数: {0}", movedFilesCount),
                    "OK");
                
                ApplicationLog.Log(
                    nameof(MotionAssetAddressableFolderRelocator),
                    ZString.Format("移動完了: {0}ファイル", movedFilesCount));
            }
            catch (System.Exception e)
            {
                EditorUtility.DisplayDialog("エラー", ZString.Format("エラーが発生しました:\n{0}", e.Message), "OK");
                ApplicationLog.LogError(
                    nameof(MotionAssetAddressableFolderRelocator),
                    ZString.Format("エラー: {0}", e));
            }
        }
        
        int MoveFilesInFolder(
            string sourceFolderRelativePath,
            string destinationFolderRelativePath,
            string destinationGroupName,
            List<string> targetIds,
            AddressableAssetSettings settings)
        {
            var movedCount = 0;
            const string assetBundlesBasePath = "Assets/GLOW/AssetBundles";
            var sourceFolderPath = Path.Combine(assetBundlesBasePath, sourceFolderRelativePath);
            var destinationFolderPath = Path.Combine(assetBundlesBasePath, destinationFolderRelativePath);
            
            // 移動元フォルダが存在しない場合はスキップ
            if (!Directory.Exists(sourceFolderPath))
            {
                ApplicationLog.Log(
                    nameof(MotionAssetAddressableFolderRelocator),
                    ZString.Format("移動元フォルダが存在しません: {0}", sourceFolderPath));
                return 0;
            }
            
            // 移動先フォルダがなければ作成
            if (!Directory.Exists(destinationFolderPath))
            {
                Directory.CreateDirectory(destinationFolderPath);
                AssetDatabase.Refresh();
                ApplicationLog.Log(
                    nameof(MotionAssetAddressableFolderRelocator),
                    ZString.Format("移動先フォルダを作成: {0}", destinationFolderPath));
            }
            
            // 移動先のAddressableグループを取得
            var destinationGroup = settings.FindGroup(destinationGroupName);
            
            if (destinationGroup == null)
            {
                ApplicationLog.LogWarning(
                    nameof(MotionAssetAddressableFolderRelocator),
                    ZString.Format("移動先のAddressableグループが見つかりません: {0}", destinationGroupName));
            }
            
            // 各IDについて処理
            foreach (var targetId in targetIds)
            {
                var files = Directory.GetFiles(sourceFolderPath, ZString.Format("*{0}*", targetId), SearchOption.TopDirectoryOnly);
                
                // .metaファイルを除外したファイル数をカウント
                var actualFiles = files.Where(f => !f.EndsWith(".meta")).ToArray();
                
                if (actualFiles.Length == 0)
                {
                    ApplicationLog.Log(
                        nameof(MotionAssetAddressableFolderRelocator),
                        ZString.Format("移動対象ファイルなし: {0} (ID: {1})", sourceFolderPath, targetId));
                    continue;
                }
                
                foreach (var sourceFilePath in files)
                {
                    // .metaファイルはスキップ
                    if (sourceFilePath.EndsWith(".meta"))
                    {
                        continue;
                    }
                    
                    var fileName = Path.GetFileName(sourceFilePath);
                    var destinationFilePath = Path.Combine(destinationFolderPath, fileName);
                    
                    // ファイル移動
                    var moveResult = AssetDatabase.MoveAsset(sourceFilePath, destinationFilePath);
                    
                    if (string.IsNullOrEmpty(moveResult))
                    {
                        movedCount++;
                        ApplicationLog.Log(
                            nameof(MotionAssetAddressableFolderRelocator),
                            ZString.Format("移動成功: {0} -> {1}", sourceFilePath, destinationFilePath));
                        
                        // Addressableの設定を更新
                        if (destinationGroup != null)
                        {
                            var assetGuid = AssetDatabase.AssetPathToGUID(destinationFilePath);
                            
                            if (!string.IsNullOrEmpty(assetGuid))
                            {
                                var entry = settings.CreateOrMoveEntry(assetGuid, destinationGroup);
                                
                                if (entry != null)
                                {
                                    // アドレス名を設定（元のアドレス名を維持するか、ファイル名ベースにするか）
                                    var addressName = Path.GetFileNameWithoutExtension(destinationFilePath);
                                    entry.SetAddress(addressName);
                                    ApplicationLog.Log(
                                        nameof(MotionAssetAddressableFolderRelocator),
                                        ZString.Format("Addressable設定を更新: {0}", addressName));
                                }
                            }
                        }
                    }
                    else
                    {
                        ApplicationLog.LogError(
                            nameof(MotionAssetAddressableFolderRelocator),
                            ZString.Format("移動失敗: {0} -> {1} (理由: {2})", sourceFilePath, destinationFilePath, moveResult));
                    }
                }
            }
            
            return movedCount;
        }
    }
}