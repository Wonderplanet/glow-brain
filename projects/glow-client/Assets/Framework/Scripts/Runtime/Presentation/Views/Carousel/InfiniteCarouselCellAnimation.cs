using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using UnityEngine;
using WPFramework.Presentation.Components;

namespace WPFramework.Presentation.Views
{
    public class InfiniteCarouselCellAnimation : ICollectionCellAnimation
    {
        const string AppearTrigger = "appear";
        const string DisappearTrigger = "disappear";

        readonly InfiniteCarouselView _view;

        public InfiniteCarouselCellAnimation(InfiniteCarouselView view)
        {
            _view = view;
        }

        void ICollectionCellAnimation.AnimateCellAppear(float cellIntervalTime, float startDelaySeconds, Action onComplete)
        {
            var cells = _view.Content
                .Cast<Transform>()
                .Where(t => t.gameObject.activeSelf)
                .OrderBy(t => t.localPosition.x)
                .ToList();
            _view.StartCoroutine(Animation(cells, AppearTrigger, true, cellIntervalTime, onComplete));
        }

        void ICollectionCellAnimation.AnimateCellDisappear()
        {
            var cells = _view.Content
                .Cast<Transform>()
                .Where(t => t.gameObject.activeSelf)
                .OrderByDescending(t => t.localPosition.x)
                .ToList();
            _view.StartCoroutine(Animation(cells, DisappearTrigger, false, 0.02f, null));
        }

        IEnumerator Animation(List<Transform> cells, string trigger, bool initWithHide, float interval, Action onComplete)
        {
            if (initWithHide)
            {
                foreach (var cell in cells) cell.gameObject.SetActive(false);
            }

            foreach (var cell in cells)
            {
                cell.gameObject.SetActive(true);

                var animator = cell.GetComponent<Animator>();
                if (animator !)
                {
                    animator.SetTrigger(trigger);
                }

                yield return new WaitForSeconds(interval);
            }

            onComplete?.Invoke();
        }
    }
}
