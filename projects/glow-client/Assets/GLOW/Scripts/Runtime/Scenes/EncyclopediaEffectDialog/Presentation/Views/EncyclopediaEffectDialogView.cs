using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaReward.Presentation.Views;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-3_キャラ図鑑ランク
    /// 　　　91-1-3-2_発動中の図鑑効果ダイアログ
    /// </summary>
    public class EncyclopediaEffectDialogView : UIView
    {
        [SerializeField] EncyclopediaRewardEffectTypeComponent _hp;
        [SerializeField] EncyclopediaRewardEffectTypeComponent _attackPower;
        [SerializeField] EncyclopediaRewardEffectTypeComponent _heal;

        public void Setup(EncyclopediaEffectDialogViewModel viewModel)
        {
            _hp.Setup(viewModel.Hp);
            _attackPower.Setup(viewModel.AttackPower);
            _heal.Setup(viewModel.Heal);

            _hp.Hidden = viewModel.Hp <= 0;
            _attackPower.Hidden = viewModel.AttackPower <= 0;
            _heal.Hidden = viewModel.Heal <= 0;
        }
    }
}
