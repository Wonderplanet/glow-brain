using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Scenes.Title.Domains.ValueObjects;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.HomeMenuSetting.Presentation.View
{
    public class HomeMenuSettingView : UIView
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct SettingButtons
        {
            public GameObject onButton;
            public GameObject offButton;

            public void SetToggleOn(bool isOn)
            {
                onButton.SetActive(isOn);
                offButton.SetActive(!isOn);
            }
        }
        
        [SerializeField] SettingButtons _settingBgmButton;
        [SerializeField] SettingButtons _settingSeButton;
        [SerializeField] SettingButtons _settingDamageDisplayButton;
        [SerializeField] UIToggleableComponentGroup _specialAttackCutInToggleableGroup;
        [SerializeField] SettingButtons _settingPushButton;
        [SerializeField] UIText _versionNumberText;
        
        public void SetBgmToggleOn(bool isOn)
        {
            _settingBgmButton.SetToggleOn(isOn);
        }
        
        public void SetSeToggleOn(bool isOn)
        {
            _settingSeButton.SetToggleOn(isOn);
        }
        
        public void SetSpecialAttackCutInToggleOn(SpecialAttackCutInPlayType specialAttackCutInPlayType)
        {
            _specialAttackCutInToggleableGroup.SetToggleOn(specialAttackCutInPlayType.ToString());
        }
        
        public void SetPushToggleOn(bool isOn)
        {
            _settingPushButton.SetToggleOn(isOn);
        }

        public void SetDamageDisplayToggleOn(bool isOn)
        {
            _settingDamageDisplayButton.SetToggleOn(isOn);
        }
        
        public void SetAppVersionText(ApplicationVersion version)
        {
            _versionNumberText.SetText("バージョン：{0}", version.ToString());
        }
    }
}