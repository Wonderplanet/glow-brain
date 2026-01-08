using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class RewardBoxWindowLayerComponent : UIObject
    {
        [SerializeField] Button _closeButton;

        [SerializeField] RewardListWindowComponent _rewardListWindowComponent;

        public Action<PlayerResourceIconViewModel> OnSelectRewardInWindow { set; get; }

        bool _isDisplaying;

        public async UniTask SetupWindowComponent(IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, RectTransform windowPosition, CancellationToken cancellationToken)
        {
            _closeButton.onClick.RemoveAllListeners();
            _closeButton.onClick.AddListener(() =>
            {
                _isDisplaying = false;
            });

            // 吹き出しに表示するアイコンの設定
            _rewardListWindowComponent.Setup(playerResourceIconViewModels, OnSelectRewardInWindow);

            // 吹き出しの位置座標の調整
            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                RectTransform,
                RectTransformUtility.WorldToScreenPoint(null, windowPosition.position),
                null,
                out Vector2 windowDisplayOis);
            _rewardListWindowComponent.RectTransform.anchoredPosition = windowDisplayOis;

            // 吹き出し表示開始
            _isDisplaying = true;
            ShowRewardWindow();

            // 吹き出し以外の画面のどこかを押した時に吹き出しを閉じる
            await UniTask.WaitUntil(() => !_isDisplaying, cancellationToken: cancellationToken);
            await CloseRewardWindow(cancellationToken);
        }

        void ShowRewardWindow()
        {
            Hidden = false;
            _rewardListWindowComponent.Hidden = false;
            _rewardListWindowComponent.ShowWindow();
            _closeButton.interactable = true;
        }

        async UniTask CloseRewardWindow(CancellationToken cancellationToken)
        {
            _closeButton.interactable = false;
            await _rewardListWindowComponent.CloseWindow(cancellationToken);
            if(_rewardListWindowComponent != null)
                _rewardListWindowComponent.Hidden = true;

            Hidden = true;
        }
    }
}
