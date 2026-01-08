using System.Collections.Generic;
using System.IO;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.UnityFindSupport
{
    public class FindReferences
    {
        public static string[] SearchPaths = { "Assets" };

        static List<string> CollectDependenciesPaths(string targetPath)
        {
            List<string> list = new List<string>();
            foreach (string path in SearchPaths)
            {
                if (Directory.Exists(path))
                    list.AddRange(Directory.GetFiles(path, "*.prefab", SearchOption.AllDirectories));
            }

            List<string> dependencies = new List<string>();
            foreach (string path in list)
            {
                string[] results = AssetDatabase.GetDependencies(new [] { path });
                if (System.Array.Find(results, r => r == targetPath) == null) continue;
                dependencies.Add(path);
            }

            return dependencies;
        }

        static void ShowDependenciesFromAssets(Object targetObject)
        {
            Debug.Log("<color=yellow>Find Dependencies in Assets start</color> =>");

            string targetPath = AssetDatabase.GetAssetPath(targetObject);
            foreach (var path in CollectDependenciesPaths(targetPath))
                Debug.Log(path);

            Debug.Log("=> <color=yellow>Find Dependencies in Assets end</color>");
        }

        static void ShowReferenceFromAssets(Object targetObject)
        {
            Debug.Log("<color=yellow>Find Reference in Assets start</color> =>");

            string targetPath = AssetDatabase.GetAssetPath(targetObject);
            string guid = AssetDatabase.AssetPathToGUID(targetPath);
            foreach (var path in CollectDependenciesPaths(targetPath))
            {
                using (var fs = new StreamReader(path))
                {
                    string line;
                    while ((line = fs.ReadLine()) != null)
                    {
                        if (!line.Contains(guid)) continue;
                        Debug.Log(path);
                        break;
                    }
                }
            }

            Debug.Log("=> <color=yellow>Find Reference in Assets end</color>");
        }

        [MenuItem("Assets/FindSupport/Find Dependencies In Assets")]
        public static void FindDependenciesInAssets()
        {
            ShowDependenciesFromAssets(Selection.activeObject);
        }

        [MenuItem("CONTEXT/Object/FindSupport/Find Dependencies in Assets")]
        public static void ContextFindDependenciesInAssets(MenuCommand command)
        {
            ShowDependenciesFromAssets(command.context);
        }

        [MenuItem("Assets/FindSupport/Find References In Assets")]
        public static void FindReferenceInAssets()
        {
            ShowReferenceFromAssets(Selection.activeObject);
        }

        [MenuItem("CONTEXT/Object/FindSupport/Find References in Assets")]
        public static void ContextFindReferenceInAssets(MenuCommand command)
        {
            ShowReferenceFromAssets(command.context);
        }

        /// <summary>Returns whether the "Find Reference/In Assets" menu item can be clicked.</summary>
        [MenuItem("Assets/FindSupport/Find Dependencies In Assets", true)]
        [MenuItem("Assets/FindSupport/Find References In Assets", true)]
        public static bool ValidateFindDependenciesInAssets()
        {
            if (Selection.activeObject == null) return false;
            if (!AssetDatabase.Contains(Selection.activeObject)) return false;
            return true;
        }

        /// <summary>Returns whether the "Find Reference in Assets" context item can be clicked.</summary>
        [MenuItem("CONTEXT/Object/FindSupport/Find Dependencies in Assets", true)]
        [MenuItem("CONTEXT/Object/FindSupport/Find References in Assets", true)]
        public static bool ValidateContextFindDependenciesInAssets(MenuCommand command)
        {
            return AssetDatabase.Contains(command.context);
        }
    }
}
