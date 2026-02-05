using System;
using System.Collections.Generic;
using System.IO;
using System.Text;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor
{
    public class PrefabTextStringFinder : EditorWindow
    {
        static readonly string[] SearchPaths = { "Assets" };
        string _textField = "";

        static List<string> CollectPrefabPaths()
        {
            List<string> list = new List<string>();
            foreach (string path in SearchPaths)
            {
                if (Directory.Exists(path))
                    list.AddRange(Directory.GetFiles(path, "*.prefab", SearchOption.AllDirectories));
            }
            return list;
        }

        [MenuItem("Window/FindSupport/Textコンポーネント内記述検索")]
        static void ShowWindow()
        {
            EditorWindow.GetWindow<PrefabTextStringFinder>("Textコンポーネント内記述検索");
        }

        void OnGUI()
        {
            GUILayout.Label("TextField");
            _textField = GUILayout.TextField(_textField);

            if (GUILayout.Button("Search"))
            {
                if (_textField != "")
                {
                    var encodeTargetText = EncodeUnicodeEscape(_textField);
                    // Debug.Log("----espace: " + encodeTargetText);
                    FindDependenciesInAssets(encodeTargetText, _textField);
                }
                else
                {
                    Debug.LogWarning("検索文章を入力してください");
                }
            }
        }

        public static void FindDependenciesInAssets(string encodedText, string baseText)
        {
            var lists = CollectPrefabPaths();

            Debug.Log("----Search Result----");
            // Debug.Log("----espace: " + encodedText);
            Debug.Log("検索: " + baseText);
            foreach (var b in lists)
            {
                var fs = new StreamReader(b).ReadToEnd();
                if (0 <= fs.IndexOf(encodedText, StringComparison.OrdinalIgnoreCase))
                {
                    Debug.Log("Path: " + b);
                }
            }
            Debug.Log("----End Search----");
        }


        string EncodeUnicodeEscape(string text)
        {
            UnicodeEncoding unicode = new UnicodeEncoding(true, false);
            string input = text;

            string output = "";
            for (int i = 0; i < input.Length; i++)
            {
                char[] c = new char[] { input[i] };
                byte[] encodedBytes = unicode.GetBytes(c);
                output += @"\u";
                for (int j = 0; j < encodedBytes.Length; j++)
                {
                    output += string.Format("{0:x2}", encodedBytes[j]);
                }
            }
            return output;
        }
    }
}
