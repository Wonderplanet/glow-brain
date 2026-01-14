using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Core.Presentation.Views.Interaction;
using GLOW.Scenes.Mission.Presentation.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Mission.Presentation.View.MissionMain
{
    public class MissionMainView : UIView
    {
        [SerializeField] Transform _contentRoot;

        [SerializeField] UIText _titleText;

        [SerializeField] UIToggleableComponentGroup _tab;

        [SerializeField] CommonLoadingView _loadingView;

        [SerializeField] RewardBoxWindowLayerComponent _rewardBoxWindowLayerComponent;

        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct BadgeComponentInfo
        {
            public string key;
            public UIImage badgeComponent;
        }
        [SerializeField] BadgeComponentInfo[] _badgeComponentInfos;

        [SerializeField] Button _bulkReceiveButton;

        [SerializeField] Button _closeButton;

        public Transform ContentRoot => _contentRoot;

        public void SetToggleOn(MissionType type)
        {
            _tab.SetToggleOn(type.ToString());
        }

        public void SetBadgeVisible(MissionType type, bool visible)
        {
            var badgeComponent = _badgeComponentInfos.Find(x => x.key == type.ToString());
            badgeComponent.badgeComponent.Hidden = !visible;
        }

        public void SetTitleText(string title)
        {
            _titleText.SetText(title);
        }

        public void StartLoading()
        {
            _loadingView.Hidden = false;
            _loadingView.StartAnimation();
        }

        public void StopLoading()
        {
            _loadingView.Hidden = true;
            _loadingView.StopAnimation();
        }

        public async UniTask ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, 
            RectTransform windowPosition, 
            CancellationToken cancellationToken)
        {
            await _rewardBoxWindowLayerComponent.SetupWindowComponent(
                playerResourceIconViewModels, 
                windowPosition, 
                cancellationToken);
        }

        public void SetOnSelectRewardInWindow(Action<PlayerResourceIconViewModel> action)
        {
            _rewardBoxWindowLayerComponent.OnSelectRewardInWindow = action;
        }

        public void SetBulkReceiveButtonVisible(bool visible)
        {
            _bulkReceiveButton.gameObject.SetActive(visible);
        }

        public void SetBulkReceiveButtonInteractable(bool interactable)
        {
            _bulkReceiveButton.interactable = interactable;
        }

        public void SetCloseButtonInteractable(bool interactable)
        {
            _closeButton.interactable = interactable;
        }
    }
}
