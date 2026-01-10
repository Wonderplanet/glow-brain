using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views
{
    public class OutpostEnhanceWindowComponent : UIObject
    {
        [SerializeField] UIImage _typeIconImage;
        [SerializeField] UIText _typeTitleText;
        [SerializeField] UIText _typeDetailText;
        [SerializeField] Button _enhanceButton;
        [SerializeField] UIText _upgradeCostText;
        [SerializeField] UIText _upgradeCostSufficientCostText;
        [SerializeField] UIObject _limitTextRoot;

        public void Setup(OutpostEnhanceTypeButtonViewModel model, Action<OutpostEnhanceTypeButtonViewModel> onButtonSelected)
        {
            _enhanceButton.onClick.RemoveAllListeners();
            _enhanceButton.onClick.AddListener(() => onButtonSelected?.Invoke(model));

            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_typeIconImage.Image, model.IconAssetPath.Value);
            _typeTitleText.SetText(model.Name.Value);
            _typeDetailText.SetText(model.Description.Value);

            if (model.IsMaxLevel)
            {
                _limitTextRoot.Hidden = false;
                _enhanceButton.gameObject.SetActive(false);
            }
            else
            {
                _limitTextRoot.Hidden = true;
                _enhanceButton.gameObject.SetActive(true);
                _upgradeCostText.SetText(model.Cost.ToStringSeparated());
                _upgradeCostSufficientCostText.SetText(model.Cost.ToStringSeparated());
                if (model.IsCostSufficient)
                {
                    _upgradeCostText.Hidden = true;
                    _upgradeCostSufficientCostText.Hidden = false;
                }
                else
                {
                    _upgradeCostText.Hidden = false;
                    _upgradeCostSufficientCostText.Hidden = true;
                }
            }
        }
    }
}
