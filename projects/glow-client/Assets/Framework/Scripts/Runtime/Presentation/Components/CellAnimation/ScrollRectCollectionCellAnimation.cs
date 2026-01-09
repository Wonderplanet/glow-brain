using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using DG.Tweening;
using UnityEngine;
using UnityEngine.UI;

namespace WPFramework.Presentation.Components
{
    public class ScrollRectCollectionCellAnimation : ICollectionCellAnimation
    {
        const float AnimationTime = 0.15f;

        readonly ScrollRect _view = null;

        public ScrollRectCollectionCellAnimation(ScrollRect view)
        {
            _view = view;
        }

        public void AnimateCellAppear(float cellIntervalTime = 0.1f, float startDelaySeconds = 0f, Action onComplete = null)
        {
            var cells = _view.content
                .Cast<Transform>()
                .Where(t => t.gameObject.activeSelf)
                .OrderBy(t => t.localPosition.x)
                .Select((t, index) => (t: t, index: index))
                .ToList();
            _view.StartCoroutine(PlayAppearTweenAnimation(cells, true, cellIntervalTime, startDelaySeconds, onComplete));
        }

        public void AnimateCellDisappear()
        {
            var cells = _view.content
                .Cast<Transform>()
                .ToList();

            cells.ForEach(cell => cell.gameObject.SetActive(false));
        }

        IEnumerator PlayAppearTweenAnimation(List<(Transform t, int index)> cells, bool initWithHide, float cellIntervalTime, float startDelaySeconds, Action onComplete)
        {
            var counter = 0;
            foreach (var cell in cells)
            {
                var interval = startDelaySeconds + cellIntervalTime * cell.index;
                _view.StartCoroutine(TweenAnimation(cell.t, true, interval, () => ++counter));
            }
            yield return new WaitUntil(() => cells.Count <= counter);
            onComplete?.Invoke();
        }

        IEnumerator TweenAnimation(Component cell, bool initWithHide, float interval, Action onComplete)
        {
            if (initWithHide)
            {
                cell.gameObject.SetActive(false);
            }

            yield return new WaitForSeconds(interval);

            var a = cell.gameObject.GetComponent<CanvasGroup>();
            if (a)
            {
                cell.gameObject.SetActive(true);
                a.alpha = 0.0f;
                DOTween.Sequence()
                    .Join(a.DOFade(1.0f, AnimationTime))
                    .OnComplete(() => onComplete?.Invoke())
                    .Play();
            }
            else
            {
                yield return new WaitForSeconds(AnimationTime);

                cell.gameObject.SetActive(true);
                onComplete?.Invoke();
            }
        }
    }
}
