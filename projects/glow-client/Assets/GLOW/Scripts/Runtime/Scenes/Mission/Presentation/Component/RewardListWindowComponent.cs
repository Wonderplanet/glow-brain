using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class RewardListWindowComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct PlayerResourceIconViewButton
        {
            public GameObject RewardIconObject;
            public Button Button;
            public PlayerResourceIconComponent PlayerResourceIconComponent;
        }
        [SerializeField] PlayerResourceIconViewButton[] _playerResourceIconViewButtonList;

        [SerializeField] float _duration = 0.2f;

        public void Setup(IReadOnlyList<PlayerResourceIconViewModel> viewModels, Action<PlayerResourceIconViewModel> onRewardIconSelected)
        {
            // 報酬の設定
            for(var i = 0; i < viewModels.Count; i++)
            {
                if (i >= _playerResourceIconViewButtonList.Length)
                    continue;

                var viewModel = viewModels[i];
                _playerResourceIconViewButtonList[i].PlayerResourceIconComponent.Setup(viewModel);
                _playerResourceIconViewButtonList[i].RewardIconObject.SetActive(true);
                _playerResourceIconViewButtonList[i].Button.onClick.RemoveAllListeners();
                _playerResourceIconViewButtonList[i].Button.onClick.AddListener(() =>
                {
                    onRewardIconSelected?.Invoke(viewModel);
                });
            }

            // 表示に使わない部分は非表示にする
            if (viewModels.Count >= _playerResourceIconViewButtonList.Length)
                return;

            for(var i = viewModels.Count; i < _playerResourceIconViewButtonList.Length; i++)
            {
                _playerResourceIconViewButtonList[i].RewardIconObject.SetActive(false);
            }

            Hidden = true;
        }

        public void ShowWindow()
        {
            transform.localScale = new Vector3(0f, 0f, 1f);
            transform.DOScale(new Vector3(1f, 1f, 1f), _duration).SetLink(gameObject).SetEase(Ease.OutExpo);
        }

        public async UniTask CloseWindow(CancellationToken cancellationToken)
        {
            transform.localScale = new Vector3(1f, 1f, 1f);
            await transform.DOScale(new Vector3(0f, 0f, 1f), _duration).SetEase(Ease.InExpo).SetLink(gameObject).WithCancellation(cancellationToken);
        }
    }
}
