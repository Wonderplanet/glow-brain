using GLOW.Core.Presentation.Modules;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(PlayerResourceIconButtonComponent))]
    public class PlayerResourceIconDetailComponent : UIObject
    {
        [SerializeField] bool _isShowTransitionLayout = false;
        PlayerResourceIconButtonComponent _button;

        protected override void Start()
        {
            base.Start();
            _button = GetComponent<PlayerResourceIconButtonComponent>();

            if (_button != null)
            {
                _button.AdditionalButtonEvent = ShowItemDetail;
            }
        }

        void ShowItemDetail()
        {
            var viewModel = _button.IconViewModel;
            if (viewModel.IsEmpty()) return;

            if (_isShowTransitionLayout)
            {
                ItemDetailUtil.Main.ShowItemDetailView(viewModel);
            }
            else
            {
                ItemDetailUtil.Main.ShowNoTransitionLayoutItemDetailView(viewModel);
            }
        }
    }
}
