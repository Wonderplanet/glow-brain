using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Domain.Models;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class HomeFooterSpeechBalloonSwitchComponent : UIComponent
    {
        [SerializeField] Animator _adventBattleSpeechBalloonAnimator;
        [SerializeField] Animator _pvpSpeechBalloonAnimator;
        [SerializeField] float _speechBalloonAnimationInterval = 2f;

        readonly List<Animator> _speechBalloonAnimators = new();
        CancellationTokenSource _cancellationTokenSource = new();

        public void SetUpSpeechBalloons(
            HomeFooterBalloonShownFlag shouldShowAdventBattle,
            HomeFooterBalloonShownFlag shouldShowPvp)
        {
            _speechBalloonAnimators.Clear();

            gameObject.SetActive(shouldShowAdventBattle || shouldShowPvp);

            // どちらかが非表示の場合切り替えアニメは不要
            if (!shouldShowAdventBattle || !shouldShowPvp)
            {
                // 表示/非表示の切り替え
                _adventBattleSpeechBalloonAnimator.gameObject.SetActive(shouldShowAdventBattle);
                _pvpSpeechBalloonAnimator.gameObject.SetActive(shouldShowPvp);

                Dispose();

                return;
            }

            _speechBalloonAnimators.Add(_adventBattleSpeechBalloonAnimator);
            _speechBalloonAnimators.Add(_pvpSpeechBalloonAnimator);

            PlaySpeechBalloonAnimationsLoop().Forget();
        }

        async UniTask PlaySpeechBalloonAnimationsLoop()
        {
            Dispose();

            _cancellationTokenSource = new CancellationTokenSource();
            int index = 0;

            // すべて非表示＋すべてのアニメーションを止める
            foreach (var speechBalloonAnimator in _speechBalloonAnimators)
            {
                speechBalloonAnimator.gameObject.SetActive(false);
            }


            while (true)
            {
                // キャンセルトークンがキャンセルされたらループを抜ける
                if (_cancellationTokenSource.IsCancellationRequested)
                {
                    break;
                }

                var currentAnimator = _speechBalloonAnimators[index];
                if (currentAnimator == null)
                {
                    break;
                }
                GameObject obj = currentAnimator.gameObject;

                // オブジェクト表示 & アニメーション再生
                obj.SetActive(true);

                // 一定時間待つ
                await UniTask.Delay(
                    (int)(_speechBalloonAnimationInterval * 1000),
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

                if (!_speechBalloonAnimators.Any())
                {
                    break; // もしアニメーターが空ならループを抜ける
                }

                // 次のインデックス（ループ）
                index = (index + 1) % _speechBalloonAnimators.Count;
            }
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            Dispose();
        }

        void Dispose()
        {
            // キャンセルトークンをキャンセルしてリソースを解放
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }
    }
}
