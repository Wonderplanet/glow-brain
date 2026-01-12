using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceAbilityIconComponent : UIObject
    {

        [SerializeField] UIImage _abilityIcon;
        [SerializeField] UnitEnhanceAbilityDescriptionComponent _enhanceAbilityDescription;
        [SerializeField] GameObject _lockImage;
        [SerializeField] Button _button;

        public bool HiddenDescription
        {
            get => _enhanceAbilityDescription.Hidden;
            set =>_enhanceAbilityDescription.Hidden = value;
        }

        public Button.ButtonClickedEvent OnClick => _button.onClick;

        public void Setup(UnitAbility ability, bool isLock)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _abilityIcon.Image,
                ability.AbilityIconPath.Value,
                () =>
                {
                    if (!_abilityIcon) return;
                    _abilityIcon.enabled = true;
                });
            _enhanceAbilityDescription.SetText(ability.Description);
            _enhanceAbilityDescription.Hidden = true;
            _lockImage.SetActive(isLock);
        }
    }
}
