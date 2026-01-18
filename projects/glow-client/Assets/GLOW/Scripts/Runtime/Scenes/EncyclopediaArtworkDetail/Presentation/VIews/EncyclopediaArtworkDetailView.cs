using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-4_作品別原画表示
    /// 　　91-4-1_原画画面
    /// </summary>
    public class EncyclopediaArtworkDetailView : UIView
    {
        [SerializeField] EncyclopediaArtworkPageComponent artworkPageComponent;
        [SerializeField] UIText _artworkNameText;
        [SerializeField] UIText _completeEffectText;
        [SerializeField] UIObject _rightButton;
        [SerializeField] UIObject _leftButton;
        [SerializeField] GameObject _completeEffectLockObj;
        [SerializeField] GameObject _outpostArtworkUsingButton;
        [SerializeField] Button _changeOutpostArtworkButton;
        [SerializeField] EncyclopediaArtworkFragmentListComponent _fragmentListComponent;

        public EncyclopediaArtworkPageComponent ArtworkPageComponent => artworkPageComponent;

        public void Setup(
            EncyclopediaArtworkDetailViewModel viewModel,
            bool isHiddenArrowButton,
            Action<EncyclopediaArtworkFragmentListCellViewModel> onSelectFragment)
        {
            _fragmentListComponent.Setup(viewModel.ArtworkFragmentList, onSelectFragment);
            _artworkNameText.SetText(viewModel.Name.Value);
            _completeEffectText.SetText(viewModel.EffectDescription.Value);

            _completeEffectLockObj.SetActive(!viewModel.ArtworkUnlock);
            _changeOutpostArtworkButton.interactable = viewModel.ArtworkUnlock;
            _outpostArtworkUsingButton.SetActive(viewModel.ArtworkUnlock && !viewModel.IsEnableSwitchOutpostArtwork);
            _changeOutpostArtworkButton.gameObject.SetActive(!_outpostArtworkUsingButton.activeSelf);

            _rightButton.Hidden = isHiddenArrowButton;
            _leftButton.Hidden = isHiddenArrowButton;
        }
    }
}
