using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views
{
    public class OutpostEnhanceTypeButtonComponent : UIObject
    {
        [SerializeField] Button _button;
        [SerializeField] UIImage _iconImage;
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _levelTopText;
        [SerializeField] UIText _levelText;
        [SerializeField] UIObject _levelMaxText;
        [SerializeField] UIObject _costRoot;
        [SerializeField] UIText _costText;
        [SerializeField] UIText _costSufficientText;
        [SerializeField] UIText _limitText;

        public void Setup(OutpostEnhanceTypeButtonViewModel viewModel, Action<OutpostEnhanceTypeButtonViewModel> onButtonSelected)
        {
            _button.onClick.RemoveAllListeners();
            _button.onClick.AddListener(() => onButtonSelected?.Invoke(viewModel));

            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_iconImage.Image, viewModel.IconAssetPath.Value);
            _titleText.SetText(viewModel.Name.Value);
            _levelText.SetText("{0}/{1}", viewModel.Level.ToString(), viewModel.MaxLevel.ToString());

            if (viewModel.IsMaxLevel)
            {
                _levelTopText.SetColor(Color.white);
                _levelText.Hidden = true;
                _levelMaxText.Hidden = false;

                _limitText.Hidden = false;
                _costRoot.Hidden = true;
            }
            else
            {
                _levelText.Hidden = false;
                _levelMaxText.Hidden = true;

                _limitText.Hidden = true;
                _costRoot.Hidden = false;

                _costText.SetText(viewModel.Cost.ToStringSeparated());
                _costSufficientText.SetText(viewModel.Cost.ToStringSeparated());

                if (viewModel.IsCostSufficient)
                {
                    _costText.Hidden = true;
                    _costSufficientText.Hidden = false;

                    _levelTopText.SetColor(Color.white);
                    _levelText.SetColor(Color.white);
                }
                else
                {
                    _costText.Hidden = false;
                    _costSufficientText.Hidden = true;

                    _levelTopText.SetColor(Color.black);
                    _levelText.SetColor(Color.black);
                }
            }
        }
    }
}
