using System.Collections.Generic;
using System.IO;
using System.Linq;
using Cysharp.Text;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.AudioChecker
{
    public class SEAudioSettingsChecker : EditorWindow
    {
        enum AudioStatus
        {
            Correct,
            IncorrectQuality,
            IncorrectMono,
            IncorrectBoth,
            Error
        }
        
        struct AudioCheckResult
        {
            public string AudioPath;
            public string AudioName;
            public AudioStatus Status;
            public float ActualQuality;
            public bool ActualForceToMono;
            public bool IsError;
            public string ErrorMessage;
        }
        
        const string AudioFolder = "Assets/GLOW/Audios/Data/SE";
        const float ExpectedQuality = 0.29999998f;
        const bool ExpectedForceToMono = true;
        
        Vector2 _scrollPosition;
        List<AudioCheckResult> _checkResults = new List<AudioCheckResult>();
        bool _showOnlyProblems = true;
        
        [MenuItem("GLOW/Check/SE Audio Settings/Show Window")]
        static void ShowWindow()
        {
            var window = GetWindow<SEAudioSettingsChecker>();
            window.titleContent = new GUIContent("SE Audio Settings Checker");
            window.Show();
        }
        
        [MenuItem("GLOW/Check/SE Audio Settings/Run Check")]
        public static void CheckSEAudioSettingsFromMenu()
        {
            var correctAudios = new List<string>();
            var incorrectQuality = new List<string>();
            var incorrectMono = new List<string>();
            var incorrectBoth = new List<string>();
            var errors = new List<string>();
            var checkedCount = 0;

            if (!Directory.Exists(AudioFolder))
            {
                Debug.LogError(ZString.Format("Folder not found: {0}", AudioFolder));
                return;
            }

            var waveFiles = Directory.GetFiles(AudioFolder, "*.wav", SearchOption.AllDirectories);

            foreach (var wavePath in waveFiles)
            {
                checkedCount++;

                try
                {
                    var audioImporter = AssetImporter.GetAtPath(wavePath) as AudioImporter;
                    if (audioImporter == null)
                    {
                        errors.Add(ZString.Format("Failed to get AudioImporter: {0}", wavePath));
                        continue;
                    }

                    var defaultSampleSettings = audioImporter.defaultSampleSettings;
                    var qualityMatches = Mathf.Approximately(defaultSampleSettings.quality, ExpectedQuality);
                    var monoMatches = audioImporter.forceToMono == ExpectedForceToMono;

                    if (qualityMatches && monoMatches)
                    {
                        correctAudios.Add(wavePath);
                    }
                    else if (!qualityMatches && !monoMatches)
                    {
                        incorrectBoth.Add(ZString.Format("{0} (quality: {1}, forceToMono: {2})", 
                            wavePath, defaultSampleSettings.quality, audioImporter.forceToMono));
                    }
                    else if (!qualityMatches)
                    {
                        incorrectQuality.Add(ZString.Format("{0} (quality: {1})", 
                            wavePath, defaultSampleSettings.quality));
                    }
                    else
                    {
                        incorrectMono.Add(ZString.Format("{0} (forceToMono: {1})", 
                            wavePath, audioImporter.forceToMono));
                    }
                }
                catch (System.Exception ex)
                {
                    var error = ZString.Format("Failed to check audio: {0} - {1}", wavePath, ex.Message);
                    errors.Add(error);
                    Debug.LogError(error);
                }
            }

            ShowResultsInLog(
                checkedCount,
                correctAudios,
                incorrectQuality,
                incorrectMono,
                incorrectBoth,
                errors);
        }

        static void ShowResultsInLog(
            int checkedCount,
            List<string> correctAudios,
            List<string> incorrectQuality,
            List<string> incorrectMono,
            List<string> incorrectBoth,
            List<string> errors)
        {
            using (var sb = ZString.CreateStringBuilder())
            {
                sb.Append(ZString.Format("Checked {0} wave files in SE folder.\n\n", checkedCount));
                sb.Append("Expected settings:\n");
                sb.Append(ZString.Format("  quality: {0}\n", ExpectedQuality));
                sb.Append(ZString.Format("  forceToMono: {0}\n\n", ExpectedForceToMono));

                if (errors.Count > 0)
                {
                    sb.Append(ZString.Format("Errors ({0}):\n", errors.Count));
                    sb.AppendJoin("\n", errors);
                    sb.Append("\n\n");
                }

                sb.Append(ZString.Format("Correct settings: {0}\n", correctAudios.Count));
                sb.Append(ZString.Format("Incorrect quality only: {0}\n", incorrectQuality.Count));
                sb.Append(ZString.Format("Incorrect forceToMono only: {0}\n", incorrectMono.Count));
                sb.Append(ZString.Format("Both settings incorrect: {0}\n\n", incorrectBoth.Count));

                if (incorrectQuality.Count == 0 && incorrectMono.Count == 0 && 
                    incorrectBoth.Count == 0 && errors.Count == 0)
                {
                    sb.Append("All wave files have correct settings!");
                    var message = sb.ToString();
                    Debug.Log(message);
                }
                else
                {
                    if (incorrectBoth.Count > 0)
                    {
                        sb.Append(ZString.Format("Files with both settings incorrect ({0}):\n", incorrectBoth.Count));
                        sb.AppendJoin("\n", incorrectBoth);
                        sb.Append("\n\n");
                    }

                    if (incorrectQuality.Count > 0)
                    {
                        sb.Append(ZString.Format("Files with incorrect quality ({0}):\n", incorrectQuality.Count));
                        sb.AppendJoin("\n", incorrectQuality);
                        sb.Append("\n\n");
                    }

                    if (incorrectMono.Count > 0)
                    {
                        sb.Append(ZString.Format("Files with incorrect forceToMono ({0}):\n", incorrectMono.Count));
                        sb.AppendJoin("\n", incorrectMono);
                    }

                    var message = sb.ToString();
                    if (errors.Count > 0 || incorrectBoth.Count > 0)
                    {
                        Debug.LogError(message);
                    }
                    else if (incorrectQuality.Count > 0 || incorrectMono.Count > 0)
                    {
                        Debug.LogWarning(message);
                    }
                    else
                    {
                        Debug.Log(message);
                    }
                }
            }
        }
        
        void OnGUI()
        {
            EditorGUILayout.LabelField("SE Audio Settings Checker", EditorStyles.boldLabel);
            EditorGUILayout.Space();
            
            EditorGUILayout.BeginHorizontal();
            EditorGUILayout.LabelField("期待する設定:", GUILayout.Width(100));
            EditorGUILayout.LabelField(ZString.Format("quality: {0}, forceToMono: {1}", 
                ExpectedQuality, ExpectedForceToMono));
            EditorGUILayout.EndHorizontal();
            
            EditorGUILayout.Space();
            
            _showOnlyProblems = EditorGUILayout.Toggle("問題があるもののみ表示", _showOnlyProblems);
            
            if (GUILayout.Button("チェックを実行"))
            {
                RunCheck();
            }
            
            EditorGUILayout.Space();
            
            if (_checkResults.Count > 0)
            {
                var filteredResults = _showOnlyProblems
                    ? _checkResults.Where(r => r.Status != AudioStatus.Correct).ToList()
                    : _checkResults;
                    
                EditorGUILayout.LabelField(ZString.Format("チェック結果: {0} 件", filteredResults.Count));
                
                var correctCount = _checkResults.Count(r => r.Status == AudioStatus.Correct);
                var qualityCount = _checkResults.Count(r => r.Status == AudioStatus.IncorrectQuality);
                var monoCount = _checkResults.Count(r => r.Status == AudioStatus.IncorrectMono);
                var bothCount = _checkResults.Count(r => r.Status == AudioStatus.IncorrectBoth);
                var errorCount = _checkResults.Count(r => r.Status == AudioStatus.Error);
                
                EditorGUILayout.BeginHorizontal();
                GUI.color = Color.green;
                EditorGUILayout.LabelField(ZString.Format("正常: {0}", correctCount), GUILayout.Width(80));
                GUI.color = Color.yellow;
                EditorGUILayout.LabelField(ZString.Format("Quality誤: {0}", qualityCount), GUILayout.Width(100));
                EditorGUILayout.LabelField(ZString.Format("Mono誤: {0}", monoCount), GUILayout.Width(100));
                GUI.color = Color.red;
                EditorGUILayout.LabelField(ZString.Format("両方誤: {0}", bothCount), GUILayout.Width(80));
                EditorGUILayout.LabelField(ZString.Format("エラー: {0}", errorCount), GUILayout.Width(80));
                GUI.color = Color.white;
                EditorGUILayout.EndHorizontal();
                
                EditorGUILayout.Space();
                
                _scrollPosition = EditorGUILayout.BeginScrollView(_scrollPosition);
                
                foreach (var result in filteredResults)
                {
                    EditorGUILayout.BeginVertical(GUI.skin.box);
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("File:", GUILayout.Width(40));
                    if (GUILayout.Button(result.AudioName, EditorStyles.linkLabel))
                    {
                        var audio = AssetDatabase.LoadAssetAtPath<AudioClip>(result.AudioPath);
                        if (audio != null)
                        {
                            EditorGUIUtility.PingObject(audio);
                            Selection.activeObject = audio;
                        }
                    }
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Status:", GUILayout.Width(50));
                    
                    if (result.IsError)
                    {
                        GUI.color = Color.red;
                        EditorGUILayout.LabelField(result.ErrorMessage, EditorStyles.wordWrappedLabel);
                    }
                    else
                    {
                        switch (result.Status)
                        {
                            case AudioStatus.Correct:
                                GUI.color = Color.green;
                                EditorGUILayout.LabelField("設定正常", EditorStyles.wordWrappedLabel);
                                break;
                            case AudioStatus.IncorrectQuality:
                                GUI.color = Color.yellow;
                                EditorGUILayout.LabelField(ZString.Format("Quality誤り (現在: {0})", 
                                    result.ActualQuality), EditorStyles.wordWrappedLabel);
                                break;
                            case AudioStatus.IncorrectMono:
                                GUI.color = Color.yellow;
                                EditorGUILayout.LabelField(ZString.Format("ForceToMono誤り (現在: {0})", 
                                    result.ActualForceToMono), EditorStyles.wordWrappedLabel);
                                break;
                            case AudioStatus.IncorrectBoth:
                                GUI.color = Color.red;
                                EditorGUILayout.LabelField(ZString.Format("両設定誤り (quality: {0}, mono: {1})", 
                                    result.ActualQuality, result.ActualForceToMono), EditorStyles.wordWrappedLabel);
                                break;
                            case AudioStatus.Error:
                                GUI.color = Color.red;
                                EditorGUILayout.LabelField(result.ErrorMessage, EditorStyles.wordWrappedLabel);
                                break;
                        }
                    }
                    GUI.color = Color.white;
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.BeginHorizontal();
                    EditorGUILayout.LabelField("Path:", GUILayout.Width(40));
                    EditorGUILayout.SelectableLabel(
                        result.AudioPath,
                        EditorStyles.textField,
                        GUILayout.Height(EditorGUIUtility.singleLineHeight));
                    EditorGUILayout.EndHorizontal();
                    
                    EditorGUILayout.EndVertical();
                    EditorGUILayout.Space();
                }
                
                EditorGUILayout.EndScrollView();
            }
            else
            {
                EditorGUILayout.LabelField("チェックを実行してください");
            }
        }
        
        void RunCheck()
        {
            _checkResults.Clear();
            
            if (!Directory.Exists(AudioFolder))
            {
                Debug.LogError(ZString.Format("Folder not found: {0}", AudioFolder));
                return;
            }
            
            var waveFiles = Directory.GetFiles(AudioFolder, "*.wav", SearchOption.AllDirectories);
            
            foreach (var wavePath in waveFiles)
            {
                var result = new AudioCheckResult
                {
                    AudioPath = wavePath,
                    AudioName = Path.GetFileName(wavePath),
                    Status = AudioStatus.Correct,
                    IsError = false
                };
                
                try
                {
                    var audioImporter = AssetImporter.GetAtPath(wavePath) as AudioImporter;
                    if (audioImporter == null)
                    {
                        result.Status = AudioStatus.Error;
                        result.IsError = true;
                        result.ErrorMessage = "Failed to get AudioImporter";
                    }
                    else
                    {
                        var defaultSampleSettings = audioImporter.defaultSampleSettings;
                        result.ActualQuality = defaultSampleSettings.quality;
                        result.ActualForceToMono = audioImporter.forceToMono;
                        
                        var qualityMatches = Mathf.Approximately(defaultSampleSettings.quality, ExpectedQuality);
                        var monoMatches = audioImporter.forceToMono == ExpectedForceToMono;
                        
                        if (qualityMatches && monoMatches)
                        {
                            result.Status = AudioStatus.Correct;
                        }
                        else if (!qualityMatches && !monoMatches)
                        {
                            result.Status = AudioStatus.IncorrectBoth;
                        }
                        else if (!qualityMatches)
                        {
                            result.Status = AudioStatus.IncorrectQuality;
                        }
                        else
                        {
                            result.Status = AudioStatus.IncorrectMono;
                        }
                    }
                }
                catch (System.Exception ex)
                {
                    result.Status = AudioStatus.Error;
                    result.IsError = true;
                    result.ErrorMessage = ZString.Format("Failed to check: {0}", ex.Message);
                }
                
                _checkResults.Add(result);
            }
            
            var correctCount = _checkResults.Count(r => r.Status == AudioStatus.Correct);
            var qualityCount = _checkResults.Count(r => r.Status == AudioStatus.IncorrectQuality);
            var monoCount = _checkResults.Count(r => r.Status == AudioStatus.IncorrectMono);
            var bothCount = _checkResults.Count(r => r.Status == AudioStatus.IncorrectBoth);
            var errorCount = _checkResults.Count(r => r.Status == AudioStatus.Error);
            
            Debug.Log(ZString.Format(
                "チェック完了: {0} 件のwaveファイルをチェックしました。正常: {1}, Quality誤: {2}, Mono誤: {3}, 両方誤: {4}, エラー: {5}",
                _checkResults.Count,
                correctCount,
                qualityCount,
                monoCount,
                bothCount,
                errorCount));
        }
    }
}