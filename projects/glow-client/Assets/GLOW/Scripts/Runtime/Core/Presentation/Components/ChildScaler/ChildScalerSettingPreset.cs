// ReSharper disable InconsistentNaming
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    [CreateAssetMenu(fileName = "ChildScalerSetting", menuName = "GLOW/ScriptableObject/UI/ChildScalerSetting")]
    public class ChildScalerSettingPreset : ScriptableObject
    {
        public ChildScalerSetting Setting = new ();
    }
}