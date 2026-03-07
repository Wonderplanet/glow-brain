using System;
using System.IO;
using System.IO.Compression;
using System.Text;
using Cysharp.Text;
using UnityEditor;
using UnityEngine;
using WonderPlanet.ResourceManagement;

namespace GLOW.Editor.AddressableTool
{
    public class CatalogDecryptTool : EditorWindow
    {
        string _inputFilePath = "";
        string _outputFilePath = "";
        string _encryptKey = "";
        string _catalogHash = "";
        
        [MenuItem("GLOW/Addressable/Catalog Decrypt Tool")]
        static void ShowWindow()
        {
            var window = GetWindow<CatalogDecryptTool>("Catalog Decrypt Tool");
            window.Show();
        }
        
        void OnGUI()
        {
            GUILayout.Label("Catalog Decrypt Tool", EditorStyles.boldLabel);
            GUILayout.Space(10);
            
            GUILayout.Label("暗号化されたカタログファイル(.data)を平文のJSONに戻します");
            GUILayout.Space(10);
            
            // Input file
            GUILayout.BeginHorizontal();
            GUILayout.Label("入力ファイル (.data):", GUILayout.Width(150));
            _inputFilePath = GUILayout.TextField(_inputFilePath);
            if (GUILayout.Button("選択", GUILayout.Width(60)))
            {
                var path = EditorUtility.OpenFilePanel("暗号化カタログファイルを選択", "", "data");
                if (!string.IsNullOrEmpty(path))
                {
                    _inputFilePath = path;
                    // 自動的に出力ファイル名を設定
                    _outputFilePath = path.Replace(".data", "_decrypted.json");
                }
            }
            GUILayout.EndHorizontal();
            
            // Output file
            GUILayout.BeginHorizontal();
            GUILayout.Label("出力ファイル (.json):", GUILayout.Width(150));
            _outputFilePath = GUILayout.TextField(_outputFilePath);
            if (GUILayout.Button("選択", GUILayout.Width(60)))
            {
                var path = EditorUtility.SaveFilePanel("出力先を選択", "", "catalog_decrypted.json", "json");
                if (!string.IsNullOrEmpty(path))
                {
                    _outputFilePath = path;
                }
            }
            GUILayout.EndHorizontal();
            
            // Encrypt key
            GUILayout.BeginHorizontal();
            GUILayout.Label("暗号化キー:", GUILayout.Width(150));
            _encryptKey = GUILayout.TextField(_encryptKey);
            GUILayout.EndHorizontal();
            
            // Catalog hash
            GUILayout.BeginHorizontal();
            GUILayout.Label("カタログハッシュ:", GUILayout.Width(150));
            _catalogHash = GUILayout.TextField(_catalogHash);
            GUILayout.EndHorizontal();
            
            GUILayout.Space(5);
            GUILayout.Label("※ハッシュは対応する.hashファイルから取得できます", EditorStyles.miniLabel);
            
            // Auto load hash button
            if (!string.IsNullOrEmpty(_inputFilePath))
            {
                var hashPath = _inputFilePath.Replace(".data", ".hash");
                if (File.Exists(hashPath))
                {
                    if (GUILayout.Button("ハッシュファイルから自動読み込み"))
                    {
                        _catalogHash = File.ReadAllText(hashPath).Trim();
                        Debug.Log(ZString.Format("ハッシュを読み込みました: {0}", _catalogHash));
                    }
                }
            }
            
            GUILayout.Space(20);
            
            // Decrypt button
            GUI.enabled = !string.IsNullOrEmpty(_inputFilePath) && 
                         !string.IsNullOrEmpty(_outputFilePath) && 
                         !string.IsNullOrEmpty(_encryptKey);
            
            if (GUILayout.Button("復号化", GUILayout.Height(30)))
            {
                try
                {
                    DecryptCatalog();
                }
                catch (Exception e)
                {
                    EditorUtility.DisplayDialog("エラー", ZString.Format("復号化に失敗しました:\n{0}", e.Message), "OK");
                    Debug.LogError(ZString.Format("復号化エラー: {0}", e));
                }
            }
            
            GUI.enabled = true;
        }
        
        void DecryptCatalog()
        {
            if (!File.Exists(_inputFilePath))
            {
                throw new FileNotFoundException(ZString.Format("入力ファイルが見つかりません: {0}", _inputFilePath));
            }
            
            var encryptedData = File.ReadAllBytes(_inputFilePath);
            var salt = GenerateSalt(_catalogHash);
            
            using var input = new MemoryStream(encryptedData);
            using var cryptor = new SeekableAesStream(input, _encryptKey, salt);
            using var decompressionStream = new GZipStream(cryptor, CompressionMode.Decompress);
            string decryptedText = null;
            using var output = new MemoryStream();
            decompressionStream.CopyTo(output);
            decryptedText = Encoding.UTF8.GetString(output.ToArray());
            
            File.WriteAllText(_outputFilePath, decryptedText);
                            
            EditorUtility.DisplayDialog("成功", ZString.Format("復号化が完了しました:\n{0}", _outputFilePath), "OK");
            Debug.Log(ZString.Format("カタログファイルを復号化しました: {0}", _outputFilePath));
        }
        
        static byte[] GenerateSalt(string hash)
        {
            return Encoding.UTF8.GetBytes(ZString.Concat(hash, "_salt"));
        }
    }
}