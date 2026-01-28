using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using UIKit;
using UnityEngine;
using UnityEngine.Events;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace WPFramework.Presentation.Components
{
    [RequireComponent(typeof(Button))]
    public class UIButtonLongPress :  MonoBehaviour, IPointerDownHandler, IPointerUpHandler
    {
        [SerializeField] UnityEvent _onPointerDown = null;
        [SerializeField] UnityEvent _onPointerUp = null;
        [SerializeField] UnityEvent _onPointerPress = null;
        [SerializeField] float _triggerdDuration = 1.0f;
        [SerializeField] float _pressInterval = 0.1f;
        [SerializeField] AnimationCurve _pressCurve = AnimationCurve.Linear(0, 1, 1, 0.5f);

        CancellationTokenSource _cancellationTokenSource = null;
        public bool IsTriggered { get; protected set; } = false;

        public UnityEvent PointerDown => _onPointerDown;
        public UnityEvent PointerUp => _onPointerUp;
        public UnityEvent PointerPress => _onPointerPress;

        public bool Interactable => _button.interactable;

        Button _button;

        void Awake()
        {
            _button = GetComponent<Button>();
        }

        async void IPointerDownHandler.OnPointerDown(PointerEventData eventData)
        {
            if (!IsValid())
            {
                return;
            }

            try
            {
                Cancel();

                _cancellationTokenSource =
                    CancellationTokenSource.CreateLinkedTokenSource(this.GetCancellationTokenOnDestroy());

                await LongPress(_cancellationTokenSource.Token);
            }
            catch (OperationCanceledException)
            {
                // NOTE: OperationCanceledExceptionはキャンセル時に発生する例外なのでもし発生しても無視をする
            }
        }

        void IPointerUpHandler.OnPointerUp(PointerEventData eventData)
        {
            Cancel();

            if (!IsTriggered)
            {
                return;
            }
            IsTriggered = false;

            PointerUp?.Invoke();
        }

        async UniTask LongPress(CancellationToken cancellationToken)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);

            await UniTask.Delay(TimeSpan.FromSeconds(_triggerdDuration), ignoreTimeScale: true, cancellationToken: cts.Token);

            if (!IsValid())
            {
                return;
            }

            IsTriggered = true;

            // NOTE: 最初の反応通知
            PointerDown?.Invoke();

            var deltaTime = 0.0f;
            await UniTaskAsyncEnumerable.EveryUpdate()
                .Select((_, x) => x)
                .ForEachAwaitAsync(async _ =>
                {
                    deltaTime += Time.unscaledDeltaTime;
                    var duration = _pressInterval * _pressCurve.Evaluate(deltaTime);
                    await UniTask.Delay(TimeSpan.FromSeconds(duration), ignoreTimeScale: true, cancellationToken: cts.Token);

                    if (!Interactable)
                    {
                        return;
                    }

                    // NOTE: 継続的な反応
                    PointerPress?.Invoke();
                }, cts.Token);
        }

        public void Cancel()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }

        bool IsValid()
        {
            return UIButtonClickExclusiveListener.LastClickedFrameCount != Time.frameCount && Interactable;
        }
    }
}
