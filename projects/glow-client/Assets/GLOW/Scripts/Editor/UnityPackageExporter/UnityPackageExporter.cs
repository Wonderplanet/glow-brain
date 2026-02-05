using System.IO;
using UnityEditor;
using UnityEngine;

namespace WonderPlanet {
    public static class UnityPackageExporter {

        // エクスポート先のパス
        public const string ExportPath = "ExportUnityPackage";

        public static void Export (string path)
        {
            var packageName = Path.GetFileName(path);
            var exportPath = ExportPackage(path,$"{ExportPath}/{packageName}.unitypackage");
            Debug.Log("====UnityPackageの書き出しが完了しました====\n" + exportPath);
        }

        static string ExportPackage (string targetPath, string exportPath) {
            // Ensure export path.
            var dir = new FileInfo(exportPath).Directory;
            if (dir != null && !dir.Exists) {
                dir.Create();
            }

            // Export
            AssetDatabase.ExportPackage(
                targetPath,
                exportPath,
                ExportPackageOptions.Interactive | ExportPackageOptions.Recurse  | ExportPackageOptions.IncludeDependencies
            );

            return Path.GetFullPath(exportPath);
        }

    }
}
