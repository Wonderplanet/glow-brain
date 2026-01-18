using System.Collections;
using UIKit;
using UnityEngine;

namespace WPFramework.Presentation.Components
{
    public class ViewTransitionSchema : MonoBehaviour, IViewTransitionSchema
    {
        Coroutine _task = null;

        public Coroutine AppearanceTransition(bool isAppearing)
        {
            if (_task != null)
            {
                StopCoroutine(_task);
            }

            _task = StartCoroutine(Run(isAppearing));
            return _task;
        }

        IEnumerator Run(bool isAppearing)
        {
            var animator = gameObject.GetComponent<Animator>();
            if (!animator)
            {
                yield break;
            }

            var animatorObserver = gameObject.GetComponent<UIAnimatorObserver>();
            if (!animatorObserver)
            {
                animatorObserver = gameObject.AddComponent<UIAnimatorObserver>();
            }

            var finish = false;
            animatorObserver.Animate(isAppearing ? "appear" : "disappear", () => finish = true);
            yield return new WaitUntil(() => finish);
        }
    }
}
