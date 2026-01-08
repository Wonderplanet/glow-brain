using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using UIKit;
using UnityEngine;

namespace WPFramework.Presentation.Components
{
    public class AnimatorCellAnimation : ICollectionCellAnimation
    {
        const string AppearTrigger = "appear";
        const string DisappearTrigger = "disappear";

        readonly UICollectionView _view;

        public AnimatorCellAnimation(UICollectionView view)
        {
            _view = view;
        }

        public void AnimateCellAppear(float cellIntervalTime, float startDelaySeconds = 0f, Action onComplete = null)
        {
            var cells = _view.ScrollRect.content
                .Cast<Transform>()
                .Where(t => t.gameObject.activeSelf)
                .OrderByDescending(t => t.localPosition.y)
                .ToList();
            _view.StartCoroutine(Animation(cells, AppearTrigger, true, cellIntervalTime, onComplete)); //0.1f
        }

        public void AnimateCellDisappear()
        {
            var cells = _view.ScrollRect.content
                .Cast<Transform>()
                .Where(t => t.gameObject.activeSelf)
                .OrderBy(t => t.localPosition.y)
                .ToList();
            _view.StartCoroutine(Animation(cells, DisappearTrigger, false, 0.02f, null));
        }

        IEnumerator Animation(List<Transform> cells, string trigger, bool initWithHide, float interval, Action onComplete)
        {
            if (initWithHide)
            {
                foreach (var cell in cells)
                {
                    cell.gameObject.SetActive(false);
                }
            }

            foreach (var cell in cells)
            {
                cell.gameObject.SetActive(true);
                var animator = cell.GetComponent<Animator>();
                if (animator)
                {
                    animator.SetTrigger(trigger);
                }

                yield return new WaitForSeconds(interval);
            }

            onComplete?.Invoke();
        }
    }
}
