using System.Collections;
using UnityEngine;
using WPFramework.Presentation.Components;

namespace WPFramework.Presentation.Extensions
{
    public static class IUIViewTransitionExtension
    {
        public static IEnumerator PlayAsCoroutine(this IUIViewTransition transition)
        {
            var completion = false;
            transition.Play(() => completion = true);
            return new WaitUntil(() => completion);
        }
    }
}
