using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using DG.Tweening;
using UIKit;
using UnityEngine;

namespace WPFramework.Presentation.Components
{
    public class TweenCellAnimation : ICollectionCellAnimation
    {
        const float AnimationTime = 0.15f;
        const float MoveVolume = -10f;

        readonly UICollectionView _view;

        public TweenCellAnimation(UICollectionView view)
        {
            _view = view;
        }

        public void AnimateCellAppear(float cellIntervalTime = 0.1f, float startDelaySeconds = 0f, Action onComplete = null)
        {
            var collectionCellColumn = _view.Column;

            //float誤差による意図しないgrouping(orderBy/ThenBy)を避けるため、intに丸めて計算する
            var cells = _view.ScrollRect.content
                .Cast<Transform>()
                .Where(t => t.gameObject.activeSelf)
                .OrderByDescending(t => (int)t.localPosition.y)
                .ThenBy(t => (int)t.localPosition.x)
                .Select((t, index) =>
                {
                    var row = index / collectionCellColumn;
                    var column = index % collectionCellColumn;
                    return (t: t, row: row, column: column);
                })
                .ToList();

            _view.StartCoroutine(PlayAppearTweenAnimation(cellIntervalTime, startDelaySeconds, cells, onComplete));
        }
        public void AnimateCellDisappear()
        {
            var cells = _view.ScrollRect.content
                .Cast<Transform>()
                .ToList();

            foreach (var cell in cells)
            {
                cell.gameObject.SetActive(false);
            }
        }

        IEnumerator PlayAppearTweenAnimation(float cellIntervalTime, float startDelaySeconds, List<(Transform t, int row, int column)> cells, Action onComplete)
        {
            var counter = 0;
            foreach (var cell in cells)
            {
                var interval = cellIntervalTime * (cell.row + cell.column) + startDelaySeconds;
                _view.StartCoroutine(TweenAnimation(cell, true, interval, () => counter++));
            }

            yield return new WaitUntil(() => cells.Count <= counter);

            onComplete?.Invoke();
        }
        IEnumerator TweenAnimation((Transform t, int row, int column) cell, bool initWithHide, float interval, Action onComplete)
        {
            if (initWithHide)
            {
                cell.t.gameObject.SetActive(false);
            }

            yield return new WaitForSeconds(interval);

            cell.t.gameObject.SetActive(true);
            var a = cell.t.gameObject.GetComponent<CanvasGroup>();
            if (a)
            {
                a.alpha = 0.0f;
                DOTween.Sequence()
                    // .Append(cell.t.DOLocalMoveY(moveVolume, animationTime).From(true).SetEase(Ease.OutCubic))
                    .Join(a.DOFade(1.0f, AnimationTime))
                    .OnComplete(() => onComplete())
                    .Play();
            }
            else
            {
                cell.t
                    .DOLocalMoveY(MoveVolume, AnimationTime).From(true)
                    .SetEase(Ease.OutCubic)
                    .OnComplete(() => onComplete())
                    .Play();
            }
        }
    }
}
