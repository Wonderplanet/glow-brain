using GLOW.Scenes.FragmentProvisionRatio.Presentation.DestinationBanner;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.FragmentProvisionRatioLineUp;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation
{
    /// <summary>
    /// 81_アイテムBOXリスト
    /// 　81-3_アイテムBOXページダイアログ
    /// 　　81-3-5_ランダムかけらBOX提供割合ダイアログ
    /// </summary>
    public sealed class FragmentProvisionRatioView : UIView
    {
        [SerializeField] ScrollRect _rect;
        [Header("レア度別確率")]
        [SerializeField] RatioByRarityComponent _raitoRarityContent;
        [Header("排出一覧")]
        [SerializeField] FragmentProvisionRatioLineUpView fragmentProvisionRatioLineUpViewR;
        [SerializeField] FragmentProvisionRatioLineUpView fragmentProvisionRatioLineUpViewSr;
        [SerializeField] FragmentProvisionRatioLineUpView fragmentProvisionRatioLineUpViewSsr;
        [SerializeField] FragmentProvisionRatioLineUpView fragmentProvisionRatioLineUpViewUr;
        [Header("獲得先遷移")]
        [SerializeField] WhereGetMessageAreaComponent _whereGetMessageAreaComponent;

        [Header("animator")]
        [SerializeField] Animator _animator;

        public ScrollRect RootRect => _rect;
        public FragmentProvisionRatioLineUpView FragmentProvisionRatioLineUpViewR => fragmentProvisionRatioLineUpViewR;
        public FragmentProvisionRatioLineUpView FragmentProvisionRatioLineUpViewSR => fragmentProvisionRatioLineUpViewSr;
        public FragmentProvisionRatioLineUpView FragmentProvisionRatioLineUpViewSSR => fragmentProvisionRatioLineUpViewSsr;
        public FragmentProvisionRatioLineUpView FragmentProvisionRatioLineUpViewUR => fragmentProvisionRatioLineUpViewUr;

        public WhereGetMessageAreaComponent WhereGetMessageAreaComponent => _whereGetMessageAreaComponent;

        public RatioByRarityComponent RatioRarityContent => _raitoRarityContent;

        public Animator Animator => _animator;

    }
}
