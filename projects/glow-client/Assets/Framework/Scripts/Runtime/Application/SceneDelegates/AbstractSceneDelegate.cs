using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using WonderPlanet.ObservabilityKit;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace WPFramework.Application.SceneDelegates
{
    public abstract class AbstractSceneDelegate : MonoBehaviour, IInitializable, IDisposable, ISceneNavigationDelegate
    {
        [Inject] IBootstrapSceneDelegate BootstrapSceneDelegate { get; }
        [Inject] IGameInteractionFactory GameInteractionFactory { get; }

        protected bool UserInteraction { get; private set; }

        IGameInteraction _gameInteraction;

        void IInitializable.Initialize()
        {
            // NOTE: この処理が呼ばれるのはZenjectのIInitializableを通して呼ばれる
            DoAsync.Invoke(this, async (cancellationToken) =>
            {
                // NOTE: Bootstrapを担うシーンの初期化が完了するまで待機する
                await UniTask.WaitUntil(() => BootstrapSceneDelegate.IsBootstrapCompleted, cancellationToken: cancellationToken);
                // NOTE: 初期化処理は非同期も実行できるようにする
                await Initialize(cancellationToken);
                UserInteraction = true;
            });
        }

        public virtual UniTask Initialize(CancellationToken cancellationToken)
        {
            return UniTask.CompletedTask;
        }

        public virtual void Initialize()
        {
        }

        public virtual void Dispose()
        {
            UserInteraction = false;
            _gameInteraction?.Dispose();
        }

        public virtual void SceneWillDisappear()
        {
            UserInteraction = false;
        }

        public virtual void SceneWillAppear()
        {
            _gameInteraction = GameInteractionFactory.Create(GetType().Name, ObservabilityKitLogLevel.Production);
            _gameInteraction.Begin();
        }

        public virtual void SceneDidDisappear()
        {
            UserInteraction = false;
            _gameInteraction?.End();
        }

        public virtual void SceneDidAppear()
        {
        }

        public virtual bool IsScenePrepared => true;
    }
}
