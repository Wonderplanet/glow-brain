using UIKit;
using System;
using UniRx;
using UnityEngine;

namespace WPFramework.Presentation.Views
{
    public class ModalPresentationEvent
    {
        public ModalPresentationEvent(UIViewController presented, UIViewController presenting)
        {
            PresentedViewController = presented;
            PresentingViewController = presenting;
        }

        public UIViewController PresentedViewController { get; }
        public UIViewController PresentingViewController { get; }
    }

    public interface IUIModalPresentationObserver
    {
        IObservable<ModalPresentationEvent> PresentationTransitionWillBeginAsObservable();
        IObservable<ModalPresentationEvent> PresentationTransitionDidEndAsObservable();
        IObservable<ModalPresentationEvent> DismissalTransitionWillBeginAsObservable();
        IObservable<ModalPresentationEvent> DismissalTransitionDidEndAsObservable();
    }

    public class ModalPresentationObserver : IUIModalPresentationEventCallback, IUIModalPresentationObserver, IDisposable
    {
        Subject<ModalPresentationEvent> _presentationTransitionWillBeginStream = new ();
        Subject<ModalPresentationEvent> _presentationTransitionDidEndStream = new ();
        Subject<ModalPresentationEvent> _dismissalTransitionWillBeginStream = new ();
        Subject<ModalPresentationEvent> _dismissalTransitionDidEndStream = new ();

        public IObservable<ModalPresentationEvent> PresentationTransitionWillBeginAsObservable()
        {
            return _presentationTransitionWillBeginStream;
        }

        public IObservable<ModalPresentationEvent> PresentationTransitionDidEndAsObservable()
        {
            return _presentationTransitionDidEndStream;
        }

        public IObservable<ModalPresentationEvent> DismissalTransitionWillBeginAsObservable()
        {
            return _dismissalTransitionWillBeginStream;
        }

        public IObservable<ModalPresentationEvent> DismissalTransitionDidEndAsObservable()
        {
            return _dismissalTransitionDidEndStream;
        }
        void IUIModalPresentationEventCallback.OnPresentationTransitionWillBegin(UIViewController presented, UIViewController presenting)
        {
            _presentationTransitionWillBeginStream?.OnNext(new ModalPresentationEvent(presented, presenting));
        }

        void IUIModalPresentationEventCallback.OnPresentationTransitionDidEnd(UIViewController presented, UIViewController presenting, bool completed)
        {
            _presentationTransitionDidEndStream?.OnNext(new ModalPresentationEvent(presented, presenting));
        }

        void IUIModalPresentationEventCallback.OnDismissalTransitionWillBegin(UIViewController presented, UIViewController presenting)
        {
            _dismissalTransitionWillBeginStream?.OnNext(new ModalPresentationEvent(presented, presenting));
        }

        void IUIModalPresentationEventCallback.OnDismissalTransitionDidEnd(UIViewController presented, UIViewController presenting, bool completed)
        {
            _dismissalTransitionDidEndStream?.OnNext(new ModalPresentationEvent(presented, presenting));
        }

        public void Dispose()
        {
            _presentationTransitionWillBeginStream?.Dispose();
            _presentationTransitionWillBeginStream = null;
            
            _presentationTransitionDidEndStream?.Dispose();
            _presentationTransitionDidEndStream = null;
            
            _dismissalTransitionWillBeginStream?.Dispose();
            _dismissalTransitionWillBeginStream = null;
            
            _dismissalTransitionDidEndStream?.Dispose();
            _dismissalTransitionDidEndStream = null;
        }
    }
}
