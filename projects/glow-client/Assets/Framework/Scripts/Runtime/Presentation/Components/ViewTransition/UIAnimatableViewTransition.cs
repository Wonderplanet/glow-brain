using System;
using System.Collections.Generic;
using UIKit;
using UnityEngine;

namespace WPFramework.Presentation.Components
{
    public class UIAnimatableViewTransition : MonoBehaviour, IUIViewTransition
    {
        struct Binding
        {
            public Transform transform;
            public Transform placeHolder;
            public Transform originalParent;
            // originalTransform
        }

        [SerializeField] List<RectTransform> _placeHolders = null;

        UIViewController _sourceViewController = null;
        UIViewController _destinationViewController = null;
        UIAnimatorObserver _animatorObserver = null;

        bool _sourceViewWillDisappear = false;
        bool _destinationViewWillAppear = false;

        public bool IsSourceViewDisappeared { get; private set; } = false;
        public bool IsDestinationViewAppeared { get; private set; } = false;

        readonly Dictionary<GameObject, Binding> _bindings = new Dictionary<GameObject, Binding>();

        public void Initialize(UIViewController sourceViewController, UIViewController destinationViewController)
        {
            _bindings.Clear();
            _sourceViewWillDisappear = false;
            IsSourceViewDisappeared = false;
            _destinationViewWillAppear = false;
            IsDestinationViewAppeared = false;

            _sourceViewController = sourceViewController;
            _destinationViewController = destinationViewController;
            Bind("SourceViewPlaceHolder", sourceViewController.View.gameObject);
            Bind("DestinationViewPlaceHolder", destinationViewController.View.gameObject);

            _animatorObserver = this.GetComponent<UIAnimatorObserver>();
            if (!_animatorObserver)
            {
                _animatorObserver = gameObject.AddComponent<UIAnimatorObserver>();
            }
        }

        public void Bind(string placeHolder, GameObject obj)
        {
            var binding = new Binding
            {
                transform = obj.transform,
                placeHolder = FindPlaceholder(placeHolder),
                originalParent = obj.transform.parent
            };
            _bindings.Add(obj, binding);
        }

        Transform FindPlaceholder(string gameObjectName)
        {
            var p = _placeHolders.Find(t => t.gameObject.name == gameObjectName);
            if (p == null)
            {
                throw new Exception("invalid place holder name.");
            }
            return p;
        }

        public void Play(Action completion = null)
        {
            ExecuteBind();

            _animatorObserver.Animate("Play", () =>
            {
                RestoreBind();
                SourceViewWillDisappear();
                DestinationViewWillAppear();
                SourceViewDidDisappear();
                DestinationViewDidAppear();

                completion?.Invoke();

                Destroy(gameObject);
            });
        }

        void ExecuteBind()
        {
            foreach (var kvp in _bindings)
            {
                var binding = kvp.Value;
                binding.transform.SetParent(binding.placeHolder, true);
            }
        }

        void RestoreBind()
        {
            foreach (var kvp in _bindings)
            {
                var binding = kvp.Value;
                binding.transform.SetParent(binding.originalParent, true);
            }
        }

        public void SourceViewWillDisappear()
        {
            if (_sourceViewWillDisappear)
            {
                return;
            }

            _sourceViewWillDisappear = true;
            _sourceViewController.BeginAppearanceTransition(false, true);
        }

        public void SourceViewDidDisappear()
        {
            if (IsSourceViewDisappeared)
            {
                return;
            }

            IsSourceViewDisappeared = true;
            _sourceViewController.View.Hidden = true;
            _sourceViewController.EndAppearanceTransition();
        }

        public void DestinationViewWillAppear()
        {
            if (_destinationViewWillAppear)
            {
                return;
            }

            _destinationViewWillAppear = true;
            _destinationViewController.View.Hidden = false;
            _destinationViewController.BeginAppearanceTransition(true, true);
        }

        public void DestinationViewDidAppear()
        {
            if (IsDestinationViewAppeared)
            {
                return;
            }

            IsDestinationViewAppeared = true;
            _destinationViewController.EndAppearanceTransition();
        }
    }
}
