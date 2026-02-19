using System.Linq;
using UnityEditor;
using UnityEditor.AddressableAssets;
using UnityEditor.AddressableAssets.Settings;
using UnityEngine;
using WonderPlanet.ResourceManagement;
using WPFramework.Modules.Log;

namespace GLOW.Editor.AddressableBuild
{
    public static class CustomAddressableContentsBuild
    {
        const string ArgsReleaseControlTargetKey = "releaseControlTarget";

        public static void BuildForIOSFromCmd()
        {
            var releaseControlTarget = GetReleaseControlTargetFromArguments();
            // NOTE: ここでReleaseControlTargetを上書きする
            OverrideReleaseControlTarget(releaseControlTarget);
            AddressableContentsBuild.BuildForIOSFromCmd();
        }

        public static void BuildForAndroidFromCmd()
        {
            var releaseControlTarget = GetReleaseControlTargetFromArguments();
            // NOTE: ここでReleaseControlTargetを上書きする
            OverrideReleaseControlTarget(releaseControlTarget);
            AddressableContentsBuild.BuildForAndroidFromCmd();
        }

        static string GetReleaseControlTargetFromArguments()
        {
            ApplicationLog.Log(nameof(CustomAddressableContentsBuild), "GetReleaseControlTargetFromArguments");

            var optionalArgs = CommandLineReader.GetCustomArguments();
            foreach (var optionalArg in optionalArgs)
            {
                ApplicationLog.Log(nameof(CustomAddressableContentsBuild), $"{optionalArg.Key}={optionalArg.Value}");
            }

            return !optionalArgs.TryGetValue(ArgsReleaseControlTargetKey, out var arg) ? string.Empty : arg;
        }

        static void OverrideReleaseControlTarget(string releaseControlTarget)
        {
            if (string.IsNullOrEmpty(releaseControlTarget))
            {
                return;
            }

            ApplicationLog.Log(nameof(CustomAddressableContentsBuild), $"OverrideReleaseControlTarget: {releaseControlTarget}");

            var settings = AddressableAssetSettingsDefaultObject.GetSettings(true);
            var customBuildScript = settings.DataBuilders
                .OfType<CustomBuildScript>()
                .FirstOrDefault();
            if (!customBuildScript)
            {
                return;
            }

            customBuildScript.releaseControlTargetKey = releaseControlTarget;
            settings.SetDirty(AddressableAssetSettings.ModificationEvent.EntryModified, null, true);
            EditorUtility.SetDirty(customBuildScript);
            AssetDatabase.SaveAssets();

            Debug.Log($"CustomBuildScript ReleaseControlTarget: {releaseControlTarget}");
        }
    }
}
