using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using UnityEngine;
using UnityEngine.Events;
using UnityEngine.EventSystems;

namespace WPFramework.Presentation.Components
{
    public sealed class LongPressRecognizer : MonoBehaviour, IPointerDownHandler, IPointerUpHandler, IPointerMoveHandler
    {
        [SerializeField] UnityEvent<PointerEventData> _onPointerDown = null;
        [SerializeField] UnityEvent<PointerEventData> _onPointerUp = null;
        [SerializeField] UnityEvent<PointerEventData> _onPointerPress = null;
        [SerializeField] float _pressInterval = 0.1f;

        CancellationTokenSource _cancellationTokenSource = null;
        public bool IsTriggered { get; private set; } = false;

        public UnityEvent<PointerEventData> PointerDown => _onPointerDown;
        public UnityEvent<PointerEventData> PointerUp => _onPointerUp;
        public UnityEvent<PointerEventData> PointerPress => _onPointerPress;

        public bool Interactable => gameObject is { activeSelf: true, activeInHierarchy: true };

        PointerEventData _pointerMoveEventData = null;

        async void IPointerDownHandler.OnPointerDown(PointerEventData eventData)
        {
            try
            {
                _cancellationTokenSource?.Cancel();
                _cancellationTokenSource?.Dispose();
                _cancellationTokenSource =
                    CancellationTokenSource.CreateLinkedTokenSource(this.GetCancellationTokenOnDestroy());

                await LongPress(_cancellationTokenSource.Token, eventData);
            }
            catch (OperationCanceledException)
            {
                // NOTE: OperationCanceledExceptionはキャンセル時に発生する例外なのでもし発生しても無視をする
            }
        }

        void IPointerUpHandler.OnPointerUp(PointerEventData eventData)
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;

            if (!IsTriggered)
            {
                return;
            }
            IsTriggered = false;

            PointerUp?.Invoke(eventData);
        }

        async UniTask LongPress(CancellationToken cancellationToken, PointerEventData eventData)
        {
            using var cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);

            if (!Interactable)
            {
                return;
            }

            IsTriggered = true;

            // NOTE: 最初の反応通知
            PointerDown?.Invoke(eventData);

            await UniTaskAsyncEnumerable.EveryUpdate()
                .Select((_, x) => x)
                .ForEachAwaitAsync(async _ =>
                {
                    await UniTask.Delay(TimeSpan.FromSeconds(_pressInterval), ignoreTimeScale: true, cancellationToken: cts.Token);

                    if (!Interactable)
                    {
                        return;
                    }

                    if (_pointerMoveEventData == null)
                    {
                        return;
                    }

                    // NOTE: 継続的な反応
                    PointerPress?.Invoke(_pointerMoveEventData);
                }, cts.Token);
        }

        void IPointerMoveHandler.OnPointerMove(PointerEventData eventData)
        {
            _pointerMoveEventData = eventData;
        }
    }
}
