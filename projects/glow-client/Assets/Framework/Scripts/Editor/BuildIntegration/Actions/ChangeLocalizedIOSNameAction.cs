using BuildIntegration;
using UnityEditor;
using UnityEngine;
using UnityEngine.Localization;
using UnityEngine.Localization.Platform.iOS;
using UnityEngine.Localization.Settings;

namespace WPFramework.BuildActions
{
    [CreateAssetMenu(menuName = "UnityBuildIntegration/Build Actions/Framework/Change Localized iOS Name", fileName = "ChangeLocalizedIOSName")]
    public sealed class ChangeLocalizedIOSNameAction : BuildAction
    {
        [SerializeField] LocalizedString _displayName;
        [SerializeField] LocalizedString _shortName;

        public override void ExecuteAction<T>(T buildProfile, BaseBuilder<T> builder)
        {
            Debug.Log("GetMetadata<AppInfo>()");
            var instance = LocalizationSettings.Instance;
            var meta = instance.GetMetadata().GetMetadata<AppInfo>();
            if (meta == null)
            {
                Debug.LogWarning("failed to get metadata");
                return;
            }

            if (_displayName is { IsEmpty: false })
            {
                // NOTE: リファレンスを設定することにより中身を書き換える
                meta.DisplayName = new LocalizedString(_displayName.TableReference, _displayName.TableEntryReference);
                Debug.Log("set display name");
            }
            else
            {
                Debug.LogWarning("failed to get display name");
            }

            if (_shortName is { IsEmpty: false })
            {
                // NOTE: リファレンスを設定することにより中身を書き換える
                meta.ShortName = new LocalizedString(_shortName.TableReference, _shortName.TableEntryReference);
                Debug.Log("set short name");
            }
            else
            {
                Debug.LogWarning("failed to get short name");
            }

            Debug.Log("Try to save assets");
            AssetDatabase.SaveAssetIfDirty(instance);
        }
    }
}
