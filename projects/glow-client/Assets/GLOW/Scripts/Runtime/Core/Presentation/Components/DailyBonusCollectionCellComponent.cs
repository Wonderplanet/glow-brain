using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.View.DailyBonusMission;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    public class DailyBonusCollectionCellComponent : UICollectionViewCell
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct EventDailyBonusStatusImage
        {
            public DailyBonusReceiveStatus Status;
            public Sprite StatusSprite;
        }

        [SerializeField] List<EventDailyBonusStatusImage> _backgroundImageInfos;
        [SerializeField] UIText _dailyText;
        [SerializeField] PlayerResourceIconComponent _rewardIconComponent;
        [SerializeField] UIImage _loginBonusBackgroundImage;
        [SerializeField] UIImage _iconEffect;
        [SerializeField] CanvasGroup _playerResourceIconCanvasGroups;
        [SerializeField] DailyBonusMissionCompleteIconAnimationComponent _dailyBonusMissionCompleteIconAnimationComponent;
        [SerializeField] Button _resourceIconButton;
        [SerializeField] UIObject _slashEffectObject;
        [SerializeField] UIImage _grayOutImage;

        protected override void Awake()
        {
            base.Awake();

            AddButton(_resourceIconButton, "resourceDetail");
        }

        public void SetUpDailyBonusCell(DailyBonusCollectionCellViewModel viewModel)
        {
            SetUpCell(
                viewModel.DailyBonusReceiveStatus,
                viewModel.LoginDayCount,
                viewModel.PlayerResourceIconViewModel);
        }

        void SetUpCell(
            DailyBonusReceiveStatus receiveStatus,
            LoginDayCount loginDayCount,
            PlayerResourceIconViewModel rewardIconViewModel)
        {
            var imageInfo = GetImageInfo(receiveStatus);
            _loginBonusBackgroundImage.Sprite = imageInfo.StatusSprite;
            _loginBonusBackgroundImage.Hidden = false;

            if (receiveStatus == DailyBonusReceiveStatus.Nothing)
            {
                _dailyText.Hidden = true;
                _iconEffect.Hidden = true;
                _rewardIconComponent.Hidden = true;
                _dailyBonusMissionCompleteIconAnimationComponent.Hidden = true;
                _resourceIconButton.gameObject.SetActive(false);
                return;
            }

            _dailyText.SetText(loginDayCount.ToLoginDayCountText());
            _rewardIconComponent.Setup(rewardIconViewModel);

            var receiving = receiveStatus == DailyBonusReceiveStatus.Receiving;
            _iconEffect.Hidden = !receiving;
            _slashEffectObject.Hidden = !receiving;

            var received = receiveStatus == DailyBonusReceiveStatus.Received;
            _playerResourceIconCanvasGroups.alpha = received ? 0.5f : 1f;
            _grayOutImage.Hidden = !received;
            _dailyBonusMissionCompleteIconAnimationComponent.Hidden = !received;
            if (received)
            {
                _dailyBonusMissionCompleteIconAnimationComponent.PlayDefAnimation();
            }
        }

        public async UniTask PlayReceiveAnimation(CancellationToken cancellationToken)
        {
            _dailyBonusMissionCompleteIconAnimationComponent.Hidden = false;
            await _dailyBonusMissionCompleteIconAnimationComponent.PlayAnimationAsync(cancellationToken);

            // 一度アニメーションが終わったら受け取り済みと同じ表示にする
            var imageInfo = GetImageInfo(DailyBonusReceiveStatus.Received);
            _loginBonusBackgroundImage.Sprite = imageInfo.StatusSprite;
            _iconEffect.Hidden = true;
            _playerResourceIconCanvasGroups.alpha = 0.5f;
            _dailyBonusMissionCompleteIconAnimationComponent.Hidden = false;
            _slashEffectObject.Hidden = true;
            _grayOutImage.Hidden = false;
            _dailyBonusMissionCompleteIconAnimationComponent.PlayDefAnimation();
        }

        EventDailyBonusStatusImage GetImageInfo(DailyBonusReceiveStatus status)
        {
            return _backgroundImageInfos.Find(info => info.Status == status);
        }
    }
}
