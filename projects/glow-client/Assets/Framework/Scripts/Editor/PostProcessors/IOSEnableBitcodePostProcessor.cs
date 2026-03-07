using UnityEditor;
using UnityEditor.Build;
using UnityEditor.Build.Reporting;
#if UNITY_IOS
using UnityEditor.iOS.Xcode;
using System.IO;
#endif  // UNITY_IOS

namespace WPFramework.PostProcessors
{
    public sealed class IOSEnableBitcodePostProcessor : IPostprocessBuildWithReport
    {
        public int callbackOrder => 0;

        public void OnPostprocessBuild(BuildReport report)
        {
            if (report.summary.platform != BuildTarget.iOS)
            {
                return;
            }

#if UNITY_IOS
            // NOTE: Xcode14からEnableBitcodeは非推奨になったので明示的にNOを指定する
            //       バージョンによってデフォルト値がYESになっていることがある
            var projectPath =
                PBXProject.GetPBXProjectPath(report.summary.outputPath);
            var project =
                new PBXProject();
            project.ReadFromString(File.ReadAllText(projectPath));
            var targets = new[]
            {
                project.GetUnityFrameworkTargetGuid(),
                project.GetUnityMainTargetGuid(),
            };
            foreach (var target in targets)
            {
                project.SetBuildProperty(target, "ENABLE_BITCODE", "NO");
            }

            File.WriteAllText(projectPath, project.WriteToString());
#endif
        }
    }
}
