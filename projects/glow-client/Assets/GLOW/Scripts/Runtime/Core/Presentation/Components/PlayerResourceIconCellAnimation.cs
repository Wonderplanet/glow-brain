using System;
using System.Collections;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Core.Presentation.Components
{
    public class PlayerResourceIconCellAnimation : IPlayerResourceIconAnimation
    {
        readonly UICollectionView _view;

        const float CellInterval = 0.07f;
        const float StartDelayTime = 0.4f;
        const float ScrollDuration = 1.2f;
        const int IconLimitCount = 24;  // アイコンが24個を超えた場合、cellアニメーションのタイミングがズレるのでそれを調整するための数値
        int _scrollRow = 1;

        Action _onComplete;

        CancellationTokenSource _cancellationTokenSource;

        public PlayerResourceIconCellAnimation(UICollectionView view)
        {
            _view = view;
        }

        public void Dispose()
        {
            StopAnimations();
        }

        public void ScrollAnimation(int viewModelCount, int startScrollRow, Action onComplete)
        {
            StopAnimations();

            _onComplete = onComplete;
            _scrollRow = startScrollRow;

            _cancellationTokenSource = new CancellationTokenSource();

            // スクロール開始
            _view.StartCoroutine(ScrollAnimationCoroutine(viewModelCount, startScrollRow));
        }

        public void SkipOneFrame()
        {
            DoAsync.Invoke(_cancellationTokenSource.Token, async cancellationToken =>
            {
                _view.ScrollRect.content.gameObject.SetActive(false);
                await UniTask.Delay(1, cancellationToken: cancellationToken);
                _view.ScrollRect.content.gameObject.SetActive(true);
            });
        }

        public void CellAnimation(IPlayerResourceIconAnimationCell cell, int index, int viewModelCount)
        {
            DoAsync.Invoke(_cancellationTokenSource.Token, async cancellationToken =>
            {
                await UniTask.Delay(1, cancellationToken: cancellationToken);
                cell.Hidden = true;
                var delayTime = CellInterval * (index + 1);
                if (index > IconLimitCount)
                {
                    delayTime = CellInterval * (index % _view.Column) + ScrollDuration;
                }

                // アニメーション開始まで待つ
                await UniTask.Delay(TimeSpan.FromSeconds(delayTime), cancellationToken: cancellationToken);
                cell.Hidden = false;
                cell.PlayAppearanceAnimation();

                if (index >= viewModelCount - 1)
                {
                    _onComplete.Invoke();
                }
            });
        }

        public void SkipAnimation()
        {
            StopAnimations();

            var scrollRect = _view.gameObject.GetComponent<ScrollRect>();
            scrollRect.verticalNormalizedPosition = 0;

            _view.ScrollRect.content.gameObject.SetActive(true);
            foreach (var cell in _view.ScrollRect.content.Cast<Transform>())
            {
                cell.gameObject.SetActive(true);
                // アニメーションを終了状態にする
                cell.GetComponent<IPlayerResourceIconAnimationCell>().PlayAppearanceAnimation(1.0f);
            }

            _onComplete.Invoke();
        }

        IEnumerator ScrollAnimationCoroutine(int viewModelCount, int startScrollRow)
        {
            var scrollRect = _view.gameObject.GetComponent<ScrollRect>();
            var collectionCellColumn = _view.Column; // 5

            // スクロール開始待ち
            var cellCountHalf = (float)startScrollRow * collectionCellColumn / 2 + StartDelayTime;
            yield return new WaitForSeconds(CellInterval * cellCountHalf);

            var scrollTime = (CellInterval * viewModelCount) -
                             (CellInterval * ((float)startScrollRow * collectionCellColumn / 2));
            var timer = 0.0f;

            // 時間計算
            while (timer < scrollTime)
            {
                timer += Time.deltaTime;

                // スクロール処理
                if (viewModelCount >= startScrollRow * collectionCellColumn)
                {
                    var scrollVerticalPosition = timer / scrollTime;
                    if (scrollVerticalPosition < 0) scrollVerticalPosition = 0;
                    scrollRect.verticalNormalizedPosition = 1 - scrollVerticalPosition;
                }
                yield return null;
            }
        }

        void StopAnimations()
        {
            _view.StopAllCoroutines();
            _cancellationTokenSource?.Cancel();
        }
    }
}
