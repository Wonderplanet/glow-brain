using GLOW.Core.Presentation.Components;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views
{
    public class OutpostEnhanceAnimationWindowComponent : UIObject
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIImage _iconImage;
        [SerializeField] UIText _beforeLevelText;
        [SerializeField] UIText _afterLevelText;
        [SerializeField] UIText _afterMaxLevelText;

        public void Setup(OutpostEnhanceResultViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_iconImage.Image, viewModel.IconAssetPath.Value);
            _titleText.SetText(viewModel.Name.Value);
            _beforeLevelText.SetText(viewModel.BeforeLevel.ToString());
            _afterLevelText.SetText(viewModel.AfterLevel.ToString());

            if (viewModel.IsMaxLevel)
            {
                _afterLevelText.Hidden = true;
                _afterMaxLevelText.Hidden = false;
            }
            else
            {
                _afterLevelText.Hidden = false;
                _afterMaxLevelText.Hidden = true;
            }
        }
    }
}
