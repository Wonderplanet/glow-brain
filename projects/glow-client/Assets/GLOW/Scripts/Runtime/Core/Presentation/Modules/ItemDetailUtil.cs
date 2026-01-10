using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using UIKit;
using Zenject;

namespace GLOW.Core.Presentation.Modules
{
    public sealed class ItemDetailUtil : IDisposable
    {
        [Inject] UICanvas Canvas { get; }
        [Inject] IItemDetailWireFrame WireFrame { get; }

        public static ItemDetailUtil Main { get; private set; }

        public ItemDetailUtil()
        {
            Main = this;
        }

        UIViewController RootViewController => Canvas.RootViewController;

        void IDisposable.Dispose()
        {
            Main = null;
        }

        public void ShowItemDetailView(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            UIViewController viewController = null)
        {
            WireFrame.ShowItemDetailView(type, id, amount, viewController ?? RootViewController);
        }

        public void ShowNoTransitionLayoutItemDetailView(ResourceType type, MasterDataId id, PlayerResourceAmount amount)
        {
            WireFrame.ShowNoTransitionLayoutItemDetailView(type, id, amount, RootViewController);
        }

        public void ShowItemDetailView(PlayerResourceIconViewModel viewModel)
        {
            WireFrame.ShowItemDetailView(viewModel, RootViewController);
        }

        public void ShowNoTransitionLayoutItemDetailView(PlayerResourceIconViewModel viewModel)
        {
            WireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, RootViewController);
        }
    }
}
