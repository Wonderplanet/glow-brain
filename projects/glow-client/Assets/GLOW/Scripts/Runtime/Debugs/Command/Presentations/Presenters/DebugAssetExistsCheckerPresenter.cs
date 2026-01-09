#if GLOW_DEBUG
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations.ViewModels;
using GLOW.Debugs.Command.Presentations.Views;

using System;
using System.Collections.Generic;
using System.Linq;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Debugs.Command.Presentations.Views.DebugAssetExistsCheckerView;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Debugs.Command.Presentations.Presenters
{
    public sealed class DebugAssetExistsCheckerPresenter : IDebugAssetExistsCheckerViewDelegate
    {
        [Inject] DebugAssetExistsCheckerUseCase DebugAssetExistsCheckerUseCase { get; }
        [Inject] DebugAssetExistsCheckerViewController ViewController { get; }
        void IDebugAssetExistsCheckerViewDelegate.Init()
        {
            ViewController.SetViewModel(DebugAssetExistsCheckerUseCase.GetAssetExistsCheckText());
        }
    }
}
#endif //GLOW_DEBUG
