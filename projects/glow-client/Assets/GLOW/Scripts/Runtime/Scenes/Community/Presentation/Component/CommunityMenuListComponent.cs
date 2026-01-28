using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Community.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Community.Presentation.Component
{
    public class CommunityMenuListComponent : UIObject
    {
        [SerializeField] Button _button;
        Button.ButtonClickedEvent OnButtonTapped => _button.onClick;
        
        public void SetBannerButton(CommunityMenuCellViewModel viewModel, Action<CommunityMenuCellViewModel> onCommunityBannerSelected)
        {
            OnButtonTapped.RemoveAllListeners();
            OnButtonTapped.AddListener(() =>
            {
                onCommunityBannerSelected(viewModel);
            });
        }
    }
}