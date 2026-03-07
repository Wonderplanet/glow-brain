using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.AddressableTool
{
    /// <summary>
    /// Addressableのアドレスに"!数字"パターンが含まれているかをチェックするEditor拡張
    /// 例: unit_icon_sp_l!202512010 のような形式を検出する
    /// </summary>
    public class AddressableAddressChecker : EditorWindow
    {
        // チェック対象のAddressable Asset Groupsが格納されているディレクトリ
        const string TargetDirectory = "Assets/AddressableAssetsData/AssetGroups";

        /// <summary>
        /// アドレスの状態を表すEnum
        /// </summary>
        public enum AddressStatus
        {
            Valid,              // 正常（!数字パターンが含まれていない）
            ContainsFolderName, // 問題あり（!数字パターンが含まれている）
        }

        /// <summary>
        /// Asset Group単位のチェック結果を保持する構造体
        /// </summary>
        struct AssetGroupCheckResult
        {
            public string AssetGroupFilePath;              // assetファイルのパス
            public string GroupName;                       // グループ名
            public List<AddressEntryResult> Entries;       // エントリのリスト
            public bool HasProblems;                       // 問題があるエントリが含まれているか
        }

        /// <summary>
        /// 個々のエントリのチェック結果を保持する構造体
        /// </summary>
        struct AddressEntryResult
        {
            public string GUID;           // アセットのGUID
            public string Address;        // アドレス名
            public AddressStatus Status;  // チェック結果
        }

        // ウィンドウのスクロール位置
        Vector2 _scrollPosition;

        // 全てのチェック結果を保持するリスト
        List<AssetGroupCheckResult> _checkResults = new List<AssetGroupCheckResult>();

        // 問題があるファイルのみを表示するかどうかのフラグ
        bool _showOnlyProblems = true;

        /// <summary>
        /// メニューから即座にチェックを実行し、結果をConsoleに出力する
        /// メニューパス: GLOW > Check > Addressable Address Check > Run Check
        /// </summary>
        [MenuItem("GLOW/Check/Addressable Address Check/Run Check")]
        public static void CheckAddressFromMenu()
        {
            // AssetGroupsディレクトリ内の全.assetファイルを取得
            var assetFiles = Directory.GetFiles(TargetDirectory, "*.asset", SearchOption.TopDirectoryOnly);

            var totalEntries = 0;
            var entriesWithExclamationNumber = new List<string>();
            var validEntries = new List<string>();

            // 各assetファイルをチェック
            foreach (var assetFile in assetFiles)
            {
                // assetファイルからエントリ情報をパースする
                var entries = ParseAssetGroupFile(assetFile);

                foreach (var entry in entries)
                {
                    totalEntries++;

                    // !数字パターンが含まれている場合は問題ありリストに追加
                    if (entry.Status == AddressStatus.ContainsFolderName)
                    {
                        entriesWithExclamationNumber.Add(ZString.Format(
                            "{0} - GUID: {1}, Address: {2}",
                            Path.GetFileName(assetFile),
                            entry.GUID,
                            entry.Address));
                    }
                    else
                    {
                        // 正常な場合はvalidリストに追加
                        validEntries.Add(ZString.Format(
                            "{0} - GUID: {1}, Address: {2}",
                            Path.GetFileName(assetFile),
                            entry.GUID,
                            entry.Address));
                    }
                }
            }

            // 結果をConsoleに出力
            ShowResultsInLog(assetFiles.Length, totalEntries, validEntries, entriesWithExclamationNumber);
        }

        /// <summary>
        /// チェッカーウィンドウを表示する
        /// メニューパス: GLOW > Check > Addressable Address Check > Show Window
        /// </summary>
        [MenuItem("GLOW/Check/Addressable Address Check/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<AddressableAddressChecker>();
            window.titleContent = new GUIContent("Address Checker");
            window.Show();
        }

        /// <summary>
        /// assetファイルをパースして、エントリ情報を取得する
        /// YAMLフォーマットのassetファイルから、m_GUIDとm_Addressの組み合わせを抽出する
        /// </summary>
        /// <param name="filePath">パース対象のassetファイルのパス</param>
        /// <returns>エントリ情報のリスト</returns>
        static List<AddressEntryResult> ParseAssetGroupFile(string filePath)
        {
            var results = new List<AddressEntryResult>();

            if (!File.Exists(filePath))
            {
                return results;
            }

            try
            {
                var content = File.ReadAllText(filePath);
                var lines = content.Split('\n');

                string currentGuid = null;
                string currentAddress = null;

                for (int i = 0; i < lines.Length; i++)
                {
                    var line = lines[i].Trim();

                    // m_GUIDの行を見つけて値を取得
                    // 例: - m_GUID: 08af712e3c57c47f5b9d8a08835a2bed
                    if (line.StartsWith("- m_GUID:"))
                    {
                        var parts = line.Split(':');
                        if (parts.Length >= 2)
                        {
                            currentGuid = parts[1].Trim();
                        }
                    }
                    // m_Addressの行を見つけて値を取得
                    // 例: m_Address: unit_icon_sp_l!202512010
                    else if (line.StartsWith("m_Address:"))
                    {
                        var parts = line.Split(':');
                        if (parts.Length >= 2)
                        {
                            currentAddress = parts[1].Trim();

                            // GUIDとAddressが両方揃ったら結果に追加
                            if (!string.IsNullOrEmpty(currentGuid) && !string.IsNullOrEmpty(currentAddress))
                            {
                                // !数字パターンが含まれているかチェック
                                var status = ContainsExclamationNumber(currentAddress)
                                    ? AddressStatus.ContainsFolderName
                                    : AddressStatus.Valid;

                                results.Add(new AddressEntryResult
                                {
                                    GUID = currentGuid,
                                    Address = currentAddress,
                                    Status = status
                                });

                                // 次のエントリのために変数をリセット
                                currentGuid = null;
                                currentAddress = null;
                            }
                        }
                    }
                }
            }
            catch (System.Exception ex)
            {
                Debug.LogError(ZString.Format("Failed to parse asset group file: {0} - {1}", filePath, ex.Message));
            }

            return results;
        }

        /// <summary>
        /// チェック結果をConsoleに出力する
        /// 問題があるエントリがある場合はWarningで、ない場合はInfoで出力する
        /// </summary>
        /// <param name="totalFiles">チェック対象のファイル数</param>
        /// <param name="totalEntries">チェック対象のエントリ数</param>
        /// <param name="validEntries">正常なエントリのリスト</param>
        /// <param name="entriesWithExclamationNumber">!数字パターンを含むエントリのリスト</param>
        static void ShowResultsInLog(
            int totalFiles,
            int totalEntries,
            List<string> validEntries,
            List<string> entriesWithExclamationNumber)
        {
            using (var sb = ZString.CreateStringBuilder())
            {
                // チェック対象のファイル数とエントリ数を出力
                sb.Append(ZString.Format(
                    "Checked {0} asset group files.\n",
                    totalFiles));
                sb.Append(ZString.Format("Total entries checked: {0}\n\n", totalEntries));

                // 正常なエントリ数と問題があるエントリ数を出力
                sb.Append(ZString.Format("Valid addresses: {0}\n", validEntries.Count));
                sb.Append(ZString.Format("Addresses containing '!number': {0}\n\n", entriesWithExclamationNumber.Count));

                // 問題がない場合はInfoログで出力
                if (entriesWithExclamationNumber.Count == 0)
                {
                    sb.Append("All addresses are valid! No '!number' pattern found in any address.");
                    var message = sb.ToString();
                    Debug.Log(message);
                }
                else
                {
                    // 問題がある場合は、詳細をリストアップしてWarningで出力
                    sb.Append(ZString.Format(
                        "Addresses containing '!number' ({0}):\n",
                        entriesWithExclamationNumber.Count));

                    sb.AppendJoin("\n", entriesWithExclamationNumber);

                    var message = sb.ToString();
                    Debug.LogWarning(message);
                }
            }
        }

        /// <summary>
        /// EditorWindowのGUI描画処理
        /// チェック実行ボタンと結果の表示を行う
        /// </summary>
        void OnGUI()
        {
            // ウィンドウのタイトル
            EditorGUILayout.LabelField("Addressable Address Checker", EditorStyles.boldLabel);
            EditorGUILayout.Space();

            // 説明文を表示
            EditorGUILayout.HelpBox(
                "AddressableAssetsData/AssetGroups以下のassetファイルでm_Addressに'!数字'パターン（例: !202512010）が含まれているかチェックします。",
                MessageType.Info);
            EditorGUILayout.Space();

            // 問題があるもののみ表示するかどうかのトグル
            _showOnlyProblems = EditorGUILayout.Toggle("問題があるもののみ表示", _showOnlyProblems);

            // チェック実行ボタン
            if (GUILayout.Button("チェックを実行"))
            {
                RunCheck();
            }

            EditorGUILayout.Space();

            // チェック結果がある場合は表示
            if (_checkResults.Count > 0)
            {
                // フィルタリング: 問題があるもののみ表示するか、全て表示するか
                var filteredResults = _showOnlyProblems
                    ? _checkResults.Where(r => r.HasProblems).ToList()
                    : _checkResults;

                EditorGUILayout.LabelField(ZString.Format("チェック結果: {0} ファイル", filteredResults.Count));

                // 全体の統計情報を計算
                var totalEntries = _checkResults.Sum(r => r.Entries.Count);
                var validCount = _checkResults.Sum(r => r.Entries.Count(e => e.Status == AddressStatus.Valid));
                var exclamationNumberCount = _checkResults.Sum(r => r.Entries.Count(e => e.Status == AddressStatus.ContainsFolderName));

                // 統計情報を色分けして表示
                EditorGUILayout.BeginHorizontal();
                GUI.color = Color.green;
                EditorGUILayout.LabelField(ZString.Format("正常: {0}", validCount), GUILayout.Width(100));
                GUI.color = Color.yellow;
                EditorGUILayout.LabelField(ZString.Format("!数字あり: {0}", exclamationNumberCount), GUILayout.Width(150));
                GUI.color = Color.white;
                EditorGUILayout.LabelField(ZString.Format("合計: {0}", totalEntries), GUILayout.Width(100));
                EditorGUILayout.EndHorizontal();

                EditorGUILayout.Space();

                // スクロール可能なリスト表示
                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);

                // 各ファイルの結果を表示
                foreach (var fileResult in filteredResults)
                {
                    EditorGUILayout.BeginVertical(GUI.skin.box);

                    // ファイル名を表示
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("File:", GUILayout.Width(50));
                    EditorGUILayout.LabelField(Path.GetFileName(fileResult.AssetGroupFilePath), EditorStyles.boldLabel);
                    EditorGUILayout.EndHorizontal();

                    // グループ名を表示
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Group:", GUILayout.Width(50));
                    EditorGUILayout.LabelField(fileResult.GroupName);
                    EditorGUILayout.EndHorizontal();

                    // ファイル内の問題があるエントリ数を表示
                    var fileExclamationNumberCount = fileResult.Entries.Count(e => e.Status == AddressStatus.ContainsFolderName);
                    if (fileExclamationNumberCount > 0)
                    {
                        GUI.color = Color.yellow;
                        EditorGUILayout.LabelField(
                            ZString.Format("{0} entries contain '!number' in address", fileExclamationNumberCount));
                        GUI.color = Color.white;
                    }

                    // エントリの詳細を表示（フィルタリングに応じて）
                    if (_showOnlyProblems)
                    {
                        // 問題があるエントリのみ表示
                        var problemEntries = fileResult.Entries.Where(e => e.Status == AddressStatus.ContainsFolderName).ToList();
                        foreach (var entry in problemEntries)
                        {
                            ShowEntryResult(entry);
                        }
                    }
                    else
                    {
                        // 全てのエントリを表示
                        foreach (var entry in fileResult.Entries)
                        {
                            ShowEntryResult(entry);
                        }
                    }

                    EditorGUILayout.EndVertical();
                    EditorGUILayout.Space();
                }

                EditorGUILayout.EndScrollView();
            }
            else
            {
                // チェック未実行の場合はメッセージを表示
                EditorGUILayout.LabelField("チェックを実行してください");
            }
        }

        /// <summary>
        /// 個々のエントリの結果を表示する
        /// 問題がある場合は警告マーク、正常な場合はチェックマークを表示
        /// </summary>
        /// <param name="entry">表示するエントリ情報</param>
        void ShowEntryResult(AddressEntryResult entry)
        {
            EditorGUILayout.BeginHorizontal();
            EditorGUILayout.Space(20, false);

            // ステータスに応じてアイコンと色を変更
            if (entry.Status == AddressStatus.ContainsFolderName)
            {
                // 問題がある場合は黄色の警告マーク
                GUI.color = Color.yellow;
                EditorGUILayout.LabelField("⚠", GUILayout.Width(20));
            }
            else
            {
                // 正常な場合は緑色のチェックマーク
                GUI.color = Color.green;
                EditorGUILayout.LabelField("✓", GUILayout.Width(20));
            }

            GUI.color = Color.white;

            // GUIDとアドレスを表示
            EditorGUILayout.LabelField(ZString.Format("GUID: {0}", entry.GUID), GUILayout.Width(280));
            EditorGUILayout.LabelField(ZString.Format("Address: {0}", entry.Address));

            EditorGUILayout.EndHorizontal();
        }

        /// <summary>
        /// ウィンドウからチェックを実行する
        /// 全assetファイルをパースして結果を_checkResultsに格納する
        /// </summary>
        void RunCheck()
        {
            // 前回の結果をクリア
            _checkResults.Clear();

            // AssetGroupsディレクトリ内の全.assetファイルを取得
            var assetFiles = Directory.GetFiles(TargetDirectory, "*.asset", SearchOption.TopDirectoryOnly);

            // 各assetファイルをチェック
            foreach (var assetFile in assetFiles)
            {
                // assetファイルからエントリ情報をパースする
                var entries = ParseAssetGroupFile(assetFile);
                // グループ名を抽出
                var groupName = ExtractGroupName(assetFile);

                // 問題があるエントリが含まれているかチェック
                var hasProblems = entries.Any(e => e.Status == AddressStatus.ContainsFolderName);

                // チェック結果を追加
                _checkResults.Add(new AssetGroupCheckResult
                {
                    AssetGroupFilePath = assetFile,
                    GroupName = groupName,
                    Entries = entries,
                    HasProblems = hasProblems
                });
            }

            // 統計情報を計算してログに出力
            var totalFiles = _checkResults.Count;
            var totalEntries = _checkResults.Sum(r => r.Entries.Count);
            var exclamationNumberCount = _checkResults.Sum(r => r.Entries.Count(e => e.Status == AddressStatus.ContainsFolderName));

            Debug.Log(ZString.Format(
                "チェック完了: {0} ファイル, {1} エントリをチェックしました。!数字あり: {2}",
                totalFiles,
                totalEntries,
                exclamationNumberCount));
        }

        /// <summary>
        /// assetファイルからグループ名を抽出する
        /// YAMLファイルから"m_GroupName:"の行を探して値を取得する
        /// </summary>
        /// <param name="filePath">assetファイルのパス</param>
        /// <returns>グループ名（取得できない場合はファイル名）</returns>
        static string ExtractGroupName(string filePath)
        {
            try
            {
                var content = File.ReadAllText(filePath);
                var lines = content.Split('\n');

                // m_GroupNameの行を探す
                foreach (var line in lines)
                {
                    if (line.Trim().StartsWith("m_GroupName:"))
                    {
                        var parts = line.Split(':');
                        if (parts.Length >= 2)
                        {
                            return parts[1].Trim();
                        }
                    }
                }
            }
            catch (System.Exception ex)
            {
                Debug.LogError(ZString.Format("Failed to extract group name from: {0} - {1}", filePath, ex.Message));
            }

            // グループ名が取得できなかった場合はファイル名を返す
            return Path.GetFileNameWithoutExtension(filePath);
        }

        /// <summary>
        /// アドレスに"!数字"パターンが含まれているかチェックする
        /// 例: "unit_icon_sp_l!202512010" → true
        ///     "unit_icon_chara_mag_00201" → false
        ///     "address!abc" → false（!の後が数字ではない）
        /// </summary>
        /// <param name="address">チェック対象のアドレス</param>
        /// <returns>!数字パターンが含まれている場合はtrue</returns>
        static bool ContainsExclamationNumber(string address)
        {
            // '!'が含まれているかチェック
            var index = address.IndexOf('!');
            if (index == -1)
            {
                return false;
            }

            // '!'の後に文字が続くかチェック
            var afterExclamation = address.Substring(index + 1);
            if (string.IsNullOrEmpty(afterExclamation))
            {
                return false;
            }

            // '!'の後の最初の文字が数字かチェック
            return char.IsDigit(afterExclamation[0]);
        }

    }
}
