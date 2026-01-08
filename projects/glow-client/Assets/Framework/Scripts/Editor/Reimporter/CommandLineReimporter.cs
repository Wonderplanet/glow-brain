using UnityEditor;
using UnityEngine;
using UnityEngine.Scripting;

namespace WPFramework.Reimporter
{
    [Preserve]
    public static class CommandLineReimporter
    {
        [Preserve]
        public static void Reimport()
        {
            try
            {
                AssetDatabase.StartAssetEditing();

                // 強制的にスクリプトの再コンパイルを促す
                var allScripts = AssetDatabase.FindAssets("t:asmdef");
                foreach (var guid in allScripts)
                {
                    var path = AssetDatabase.GUIDToAssetPath(guid);
                    Debug.Log("Reimporting Script : " + path);
                    AssetDatabase.ImportAsset(path, ImportAssetOptions.ForceUpdate);
                }

                var argument = CommandLineReader.GetCustomArgument("path");
                if (string.IsNullOrEmpty(argument))
                {
                    Debug.LogError("path is null");
                    return;
                }

                var paths = argument.Split(",");
                foreach (var path in paths)
                {
                    Debug.Log("Reimporting: " + path);
                    AssetDatabase.ImportAsset(path,
                        ImportAssetOptions.ImportRecursive | ImportAssetOptions.ForceUpdate);
                }
            }
            finally
            {
                AssetDatabase.StopAssetEditing();
                AssetDatabase.Refresh(ImportAssetOptions.ForceUpdate);
            }
        }
    }
}
