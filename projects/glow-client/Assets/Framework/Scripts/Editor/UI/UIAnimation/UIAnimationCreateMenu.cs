using System.Collections.Generic;
using System.IO;
using UnityEditor;
using UnityEngine;

namespace UIKit
{
    public class UIAnimationCreateMenu : MonoBehaviour
    {
        public class AnimationClipOverrides : List<KeyValuePair<AnimationClip, AnimationClip>>
        {
            public AnimationClipOverrides(int capacity) : base(capacity) { }

            public AnimationClip this[string name]
            {
                get { return this.Find(x => x.Key.name.Equals(name)).Value; }
                set
                {
                    int index = this.FindIndex(x => x.Key.name.Equals(name));
                    if (index != -1)
                        this[index] = new KeyValuePair<AnimationClip, AnimationClip>(this[index].Key, value);
                }
            }
        }

        [MenuItem("Assets/Create/UIKit/UIViewAnimations", false)]
        static void CreateAssets()
        {
            string targetPath = CreatePath();
            if (targetPath == "") return;

            Directory.CreateDirectory(targetPath);

            var original = AssetDatabase.LoadAssetAtPath<RuntimeAnimatorController>("Packages/net.wonderpla.uikit/Animations/Base/Base.controller");

            var targetName = Path.GetFileName(targetPath);
            var basePath = targetPath.Replace(Application.dataPath, "Assets");
            var controllerPath = Path.Combine(basePath, targetName + ".overrideController");

            var controller = new AnimatorOverrideController(original);
            var clipOverrides = new AnimationClipOverrides(controller.overridesCount);
            controller.GetOverrides(clipOverrides);

            clipOverrides["Base@Appeared"] = CreateClip(Path.Combine(basePath, targetName + "@Appeared.anim"));
            clipOverrides["Base@Appearing"] = CreateClip(Path.Combine(basePath, targetName + "@Appearing.anim"));
            clipOverrides["Base@Disappeared"] = CreateClip(Path.Combine(basePath, targetName + "@Disappeared.anim"));
            clipOverrides["Base@Disappearing"] = CreateClip(Path.Combine(basePath, targetName + "@Disappearing.anim"));
            controller.ApplyOverrides(clipOverrides);

            AssetDatabase.CreateAsset(controller, controllerPath);
            AssetDatabase.Refresh();
        }

        static AnimationClip CreateClip(string path)
        {
            var clip = new AnimationClip();
            AssetDatabase.CreateAsset(clip, path);
            return clip;
        }

        static string CreatePath()
        {
            var absolutePath = EditorUtility.SaveFilePanel(
                "name for view",
                GetSelectedPathInProjectsTab(),
                "UIView",
                "");

            if (absolutePath == "")
            {
                return "";
            }

            return absolutePath;

        }

        // TODO: 重複コード、リファクタ
        static string GetSelectedPathInProjectsTab()
        {
            var paths = new List<string>();

            Object[] selectedAssets = Selection.GetFiltered(
            typeof(Object), SelectionMode.Assets);

            foreach (var item in selectedAssets)
            {
                var relativePath = AssetDatabase.GetAssetPath(item);

                if (!string.IsNullOrEmpty(relativePath))
                {
                    var fullPath = Path.GetFullPath(Path.Combine(
                        Application.dataPath, Path.Combine("..", relativePath)));

                    paths.Add(fullPath);
                }
            }

            return paths[0];
        }
    }
}