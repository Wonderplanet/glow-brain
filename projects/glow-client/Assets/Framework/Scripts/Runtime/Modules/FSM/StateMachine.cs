using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.FSM
{
    public class StateMachine : IStateMachine
    {
        IState _currentState;
        IState _previousState;
        readonly Dictionary<Type, IState> _stateTable = new ();
        readonly Queue<Transition> _pendingTransitions = new ();
        CancellationTokenSource _cancellationTokenSource;

        public void AddState(IState state)
        {
            // NOTE: StateMachineのTransitionsをかなり厳しく定義することもできはするが取り回しが悪くなるのである程度自由にさせる
            state.StateTransition = this;
            _stateTable.Add(state.GetType(), state);
        }

        public void RequestTransition(Type stateType)
        {
            _pendingTransitions.Enqueue(new Transition(stateType, null));
        }

        public void RequestTransition<TOptions>(Type stateType, TOptions context) where TOptions : AbstractContext
        {
            _pendingTransitions.Enqueue(new Transition(stateType, context));
        }

        public void RequestTransition<TState, TOptions>(TOptions options) where TOptions : AbstractContext
        {
            RequestTransition(typeof(TState), options);
        }

        async UniTask ChangeTo<T>(CancellationToken cancellationToken, Type stateType, T options) where T : AbstractContext
        {
            if (cancellationToken.IsCancellationRequested)
            {
                return;
            }

            if (_currentState != null)
            {
                ApplicationLog.Log(nameof(StateMachine), $"ChangeTo: {_currentState.GetType()} -> {stateType}");

                _previousState = _currentState;
                await _previousState.OnExit(cancellationToken);
                _currentState = null;
            }

            if (_stateTable.TryGetValue(stateType, out var nextState))
            {
                nextState.SetContext(options);
                _currentState = nextState;
                await nextState.OnEnter(cancellationToken);
            }
            else
            {
                throw new Exception($"{nameof(StateMachine)} State not found: {stateType}");
            }
        }

        public void Run(CancellationToken cancellationToken)
        {
            ApplicationLog.Log(nameof(StateMachine), nameof(Run));

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);
            _cancellationTokenSource.Token.Register(() =>
            {
                _currentState?.OnCancelled();
            }, useSynchronizationContext: true);

            DoAsync.Invoke(_cancellationTokenSource.Token, async ct => await Update(ct));
        }

        public void Stop()
        {
            ApplicationLog.Log(nameof(StateMachine), nameof(Stop));

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }

        async UniTask Update(CancellationToken cancellationToken)
        {
            await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
            {
                // NOTE: キャンセルを受け取っていたら終了
                if (cancellationToken.IsCancellationRequested)
                {
                    break;
                }

                while (_pendingTransitions.Count > 0)
                {
                    // NOTE: キャンセルを受け取っていたら終了
                    if (cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    var transition = _pendingTransitions.Dequeue();
                    await ChangeTo(cancellationToken, transition.Type, transition.Context);
                }

                // NOTE: キャンセルを受け取っていたら終了
                if (cancellationToken.IsCancellationRequested)
                {
                    break;
                }

                _currentState?.OnUpdate();
            }
        }
    }
}
