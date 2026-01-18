using System.Collections;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class CampaignBalloonMultiSwitcherComponent : UIComponent
    {
        [SerializeField] CampaignBalloon _campaignBalloonPrefab;
        [SerializeField] RectTransform _campaignBalloonParent;

        readonly List<CampaignBalloon> _campaignBalloons = new();
        
        CancellationTokenSource _cancellationTokenSource = new();

        public void SetUpCampaignBalloons(IReadOnlyList<CampaignViewModel> campaignViewModels)
        {
            _campaignBalloons.Clear();
            foreach (Transform child in _campaignBalloonParent)
            {
                Destroy(child.gameObject);
            }

            if (campaignViewModels.Count <= 0)
            {
                return;
            }

            foreach (var campaignViewModel in campaignViewModels)
            {
                var campaignBalloon = Instantiate(_campaignBalloonPrefab, _campaignBalloonParent);
                campaignBalloon.SetUpContent(campaignViewModel.CampaignType);
                campaignBalloon.SetUpTitleText(campaignViewModel.Title);
                campaignBalloon.SetUpDescriptionText(campaignViewModel.Description);
                campaignBalloon.SetRemainingTimeText(campaignViewModel.RemainingTimeSpan);
                _campaignBalloons.Add(campaignBalloon);
            }

            if (_campaignBalloons.Count <= 1)
            {
                return;
            }

            PlayCampaignBalloonAnimationsLoop().Forget();
        }

        async UniTask PlayCampaignBalloonAnimationsLoop()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();

            _cancellationTokenSource = new CancellationTokenSource();
            int index = 0;

            // すべて非表示＋すべてのアニメーションを止める
            foreach (var campaignBalloon in _campaignBalloons)
            {
                campaignBalloon.gameObject.SetActive(false);
            }

            while (true)
            {
                // キャンセルトークンがキャンセルされたらループを抜ける
                if (_cancellationTokenSource.IsCancellationRequested)
                {
                    break;
                }

                var current = _campaignBalloons[index];
                if (current == null)
                {
                    break;
                }
                GameObject obj = current.gameObject;

                // オブジェクト表示 & アニメーション再生
                obj.SetActive(true);

                // アニメ終わるまで待つ
                await UniTask.WaitUntil(
                    () =>
                        current != null &&
                        current.Animator != null &&
                        current.Animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1f, 
                    cancellationToken: _cancellationTokenSource.Token);

                // キャンセルトークンがキャンセルされたらループを抜ける
                if (_cancellationTokenSource.IsCancellationRequested)
                {
                    break;
                }

                // 非表示
                if (obj != null)
                {
                    obj.SetActive(false);
                }

                // 次のインデックス（ループ）
                index = (index + 1) % _campaignBalloons.Count;
            }
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
        }
    }
}
