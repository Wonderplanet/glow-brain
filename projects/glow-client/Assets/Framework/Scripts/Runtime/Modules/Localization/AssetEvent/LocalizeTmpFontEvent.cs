using TMPro;
using UnityEngine;
using UnityEngine.Localization;
using UnityEngine.Localization.Components;

namespace WPFramework.Modules.Localization
{
    [AddComponentMenu("Localization/Asset/Localize TMP Font Event")]
    public sealed class LocalizeTmpFontEvent : LocalizedAssetEvent<TMP_FontAsset, LocalizedTmpFont, UnityEventTmpFont>
    {

    }
}
