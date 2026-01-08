using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public class InitialAssetLoader : IInitialAssetLoader, IDisposable
    {
        [Inject] IKomaBackgroundLoader KomaBackgroundLoader { get; }
        [Inject] IKomaEffectPrefabLoader KomaEffectPrefabLoader { get; }
        [Inject] IUnitAttackViewInfoSetLoader UnitAttackViewInfoSetLoader { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IMangaAnimationLoader MangaAnimationLoader { get; }
        [Inject] IOutpostViewInfoLoader OutpostViewInfoLoader { get; }
        [Inject] IDefenseTargetImageLoader DefenseTargetImageLoader { get; }
        [Inject] IInGameGimmickObjectImageLoader InGameGimmickObjectImageLoader { get; }
        [Inject] IBackgroundMusicManagement BackgroundMusicManagement { get; }
        [Inject] IFontAssetClearExecutor FontAssetClearExecutor { get; }

        InitialLoadAssetsModel _initialLoadAssetsModel = InitialLoadAssetsModel.Empty;
        CancellationTokenSource _cancellationTokenSource;

        public bool IsCompleted { get; private set; }
        
        public void Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
        }
        
        public void LoadInBackground(InitialLoadAssetsModel initialLoadAssetsModel, CancellationToken cancellationToken)
        {
            _initialLoadAssetsModel = initialLoadAssetsModel;

            Load(initialLoadAssetsModel, cancellationToken).Forget();
        }

        async UniTask Load(InitialLoadAssetsModel initialLoadAssetsModel, CancellationToken cancellationToken)
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            
            _cancellationTokenSource = new CancellationTokenSource();
            
            using var linkedCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, 
                _cancellationTokenSource.Token);
            
            var linkedCancellationToken = linkedCancellationTokenSource.Token;
                
            IsCompleted = false;
                
            var unitImageLoadTasks = initialLoadAssetsModel.UnitAssetKeys
                .Select(UnitImageAssetPath.FromAssetKey)
                .Select(imagePath => UnitImageLoader.Load(linkedCancellationToken, imagePath));

            var unitAttackViewInfoSetLoadTasks = initialLoadAssetsModel.UnitAssetKeys
                .Select(assetKey => UnitAttackViewInfoSetLoader.Load(assetKey, linkedCancellationToken));

            var komaEffectPrefabLoadTasks = initialLoadAssetsModel.KomaEffectAssetKeys
                .Select(assetKey => KomaEffectPrefabLoader.Load(assetKey, linkedCancellationToken));
                
            var komaBackgroundLoadTasks = initialLoadAssetsModel.KomaBackgroundAssetKeys
                .Select(assetKey => KomaBackgroundLoader.Load(assetKey, linkedCancellationToken));
                
            var mangaAnimationLoadTasks = initialLoadAssetsModel.MangaAnimationAssetKeys
                .Select(assetKey => MangaAnimationLoader.Load(assetKey, linkedCancellationToken));

            var outpostViewInfoLoadTasks = initialLoadAssetsModel.OutpostAssetKeys
                .Select(assetKey => OutpostViewInfoLoader.Load(assetKey, linkedCancellationToken));
                
            var gimmickObjectLoadTasks = initialLoadAssetsModel.GimmickObjectAssetKeys
                .Select(assetKey => InGameGimmickObjectImageLoader.Load(assetKey, linkedCancellationToken));
                
            var defenseTargetLoadTasks = initialLoadAssetsModel.DefenseTargetAssetKeys
                .Select(assetKey => DefenseTargetImageLoader.Load(assetKey, linkedCancellationToken));

            var bgmLoadTasks = initialLoadAssetsModel.BGMAssetKeys
                .Select(assetKey => BackgroundMusicManagement.Load(linkedCancellationToken, assetKey.Value));

            // インゲームで使用するわけではないが、Unloadタイミングで非同期処理をしたくないので事前にLoadしておく
            var fontLoadTask = FontAssetClearExecutor.LoadFonts(linkedCancellationToken);

            var loadTasks = new List<UniTask>();
            loadTasks.AddRange(unitImageLoadTasks);
            loadTasks.AddRange(unitAttackViewInfoSetLoadTasks);
            loadTasks.AddRange(komaEffectPrefabLoadTasks);
            loadTasks.AddRange(komaBackgroundLoadTasks);
            loadTasks.AddRange(mangaAnimationLoadTasks);
            loadTasks.AddRange(outpostViewInfoLoadTasks);
            loadTasks.AddRange(gimmickObjectLoadTasks);
            loadTasks.AddRange(defenseTargetLoadTasks);
            loadTasks.AddRange(bgmLoadTasks);
            loadTasks.Add(fontLoadTask);

            await UniTask.WhenAll(loadTasks);
                
            IsCompleted = true;
        }

        public void Unload()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
            
            KomaBackgroundLoader.Unload();
            KomaEffectPrefabLoader.Unload();
            UnitAttackViewInfoSetLoader.Unload();
            UnitImageLoader.Unload();
            MangaAnimationLoader.Unload();
            OutpostViewInfoLoader.Unload();
            DefenseTargetImageLoader.Unload();
            InGameGimmickObjectImageLoader.Unload();
            FontAssetClearExecutor.UnloadAndClearFontAssetData();

            foreach (var assetKey in _initialLoadAssetsModel.BGMAssetKeys)
            {
                BackgroundMusicManagement.Unload(assetKey.Value);
            }
        }
    }
}