using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.PassShopProductDetail.Presentation.Component;
using GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.View
{
    public class PassShopProductDetailView : UIView
    {
        [SerializeField] UIImage _passIconImage;
        [SerializeField] UIText _passNameText;

        [SerializeField] UIText _passProductDescriptionText;
        
        [SerializeField] UIObject _passEffectSectionTitleObject;
        [SerializeField] Transform _passEffectCellContainer;
        [SerializeField] PassEffectCellComponent _passEffectCellComponent;
        
        [SerializeField] UIObject _passRewardSectionTitleObject;
        [SerializeField] Transform _passRewardCellContainer;
        [SerializeField] PassReceivableRewardCellComponent _passReceivableRewardListCellComponent;

        public void SetupPassIcon(PassIconAssetPath passIconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _passIconImage.Image,
                passIconAssetPath.Value,
                () =>
                {
                    if (!_passIconImage) return;
                    _passIconImage.Image.SetNativeSize();
                });
        }

        public void SetPassName(PassProductName passProductName)
        {
            _passNameText.SetText(passProductName.Value);
        }

        public void SetPassProductDescription(
            PassProductName passProductName,
            PassStartAt passStartAt,
            PassEndAt passEndAt,
            DisplayExpirationFlag isDisplayExpiration,
            PassDurationDay passDurationDay,
            IReadOnlyList<PassEffectViewModel> passEffectViewModels,
            IReadOnlyList<PassReceivableRewardViewModel> passReceivableMaxRewardViewModels)
        {
            _passProductDescriptionText.SetText(
                CreateDescriptionText(
                    passProductName,
                    passStartAt,
                    passEndAt,
                    isDisplayExpiration,
                    passDurationDay,
                    passEffectViewModels,
                    passReceivableMaxRewardViewModels));
        }
        
        public void SetPassEffectSectionTitleVisible(bool isVisible)
        {
            _passEffectSectionTitleObject.IsVisible = isVisible;
        }

        public PassEffectCellComponent InstantiatePassEffectCell()
        {
            return Instantiate(_passEffectCellComponent, _passEffectCellContainer);
        }
        
        public void SetPassRewardSectionTitleVisible(bool isVisible)
        {
            _passRewardSectionTitleObject.IsVisible = isVisible;
        }

        public PassReceivableRewardCellComponent InstantiateProductListCell()
        {
            return Instantiate(_passReceivableRewardListCellComponent, _passRewardCellContainer);
        }

        string CreateDescriptionText(
            PassProductName passProductName,
            PassStartAt passStartAt,
            PassEndAt passEndAt,
            DisplayExpirationFlag isDisplayExpiration,
            PassDurationDay passDurationDay,
            IReadOnlyList<PassEffectViewModel> passEffectViewModels,
            IReadOnlyList<PassReceivableRewardViewModel> passReceivableMaxRewardViewModels)
        {
            var builder = ZString.CreateStringBuilder();
            if (isDisplayExpiration)
            {
                builder.AppendLine(ZString.Format(
                    "<color={0}>開催期間 {1} 〜 {2}</color>",
                    ColorCodeTheme.TextRed,
                    passStartAt.ToFormattedString(),
                    passEndAt.ToFormattedString()));
            }
            else
            {
                builder.AppendLine(ZString.Format(
                    "<color={0}>開催期間 期限なし</color>",
                    ColorCodeTheme.TextRed));
            }
            builder.AppendLine();
            builder.AppendLine(ZString.Format("{0}の概要", passProductName.ToString()));
            builder.AppendLine();
            builder.AppendLine("開催期間中であればいつでも購入可能です。");
            builder.AppendLine();
            
            // パス報酬文言
            builder = BuildPassRewardText(
                builder,
                passDurationDay,
                passReceivableMaxRewardViewModels);
            
            // パス効果文言
            builder = BuildPassEffectText(builder, passEffectViewModels);
            
            builder.AppendLine("【有効期間】");
            builder.AppendLine(ZString.Format(
                "・購入日を含めた最大{0}日間",
                passDurationDay.Value));
            builder.AppendLine();
            builder.AppendLine("【注意事項】");
            builder.AppendLine(ZString.Format(
                "・他OSへの機種変更を行なった場合にも、{0}の効果は引き継がれます。",
                passProductName.ToString()));
            builder.AppendLine();
            builder.AppendLine(ZString.Format(
                "・アカウント連携した他の端末でプレイを行う場合にも、{0}の効果は引き継がれます。",
                passProductName.ToString()));
            builder.AppendLine();

            var containsDiamondReward = passReceivableMaxRewardViewModels
                .Any(reward => reward.PlayerResourceIconViewModel.ResourceType == ResourceType.FreeDiamond);
            if (containsDiamondReward)
            {
                builder.AppendLine(ZString.Format(
                    "・{0}で得られるプリズムは、無償プリズムです。無償プリズムを用いて有償プリズム用サービスを利用することはできません。",
                    passProductName.ToString()));
                builder.AppendLine();
            }
            builder.AppendLine(ZString.Format(
                "・コンビニ決済では、商品を購入してから決済が完了するまでの間は{0}の効果を得ることはできません。",
                passProductName.ToString()));
            builder.AppendLine();
            builder.AppendLine(ZString.Format(
                "・{0}は返品/交換は一切お受けできません。",
                passProductName.ToString()));
            builder.AppendLine();
            builder.AppendLine(ZString.Format("・{0}日間毎日報酬の1日目の報酬は、決済完了の直後にメールBOXへ送られます。", passDurationDay.Value));
            builder.AppendLine();
            builder.Append("・2日目以降の報酬は、1日1回ゲームへのログイン時にメールBOXへ送られます。");
            return builder.ToString();
        }
        
        Utf16ValueStringBuilder BuildPassRewardText(
            Utf16ValueStringBuilder builder, 
            PassDurationDay passDurationDay,
            IReadOnlyList<PassReceivableRewardViewModel> passReceivableMaxRewardViewModels)
        {
            if (passReceivableMaxRewardViewModels.IsEmpty()) return builder;
            
            builder.AppendLine("【商品内訳】");
            foreach (var reward in passReceivableMaxRewardViewModels)
            {
                builder.AppendLine(ZString.Format(
                    "・{0} {1}個（1日{2}個 × {3}日間）",
                    reward.ProductName.ToString(),
                    reward.PlayerResourceIconViewModel.Amount.ToStringSeparated(),
                    reward.DailyReceivableAmount.ToStringSeparated(),
                    passDurationDay.Value));
            }
            builder.AppendLine();
            
            return builder;
        }
        
        Utf16ValueStringBuilder BuildPassEffectText(
            Utf16ValueStringBuilder builder, 
            IReadOnlyList<PassEffectViewModel> passEffectViewModels)
        {
            if (passEffectViewModels.IsEmpty()) return builder;
            
            builder.AppendLine("【獲得効果】");
            foreach (var effect in passEffectViewModels)
            {
                builder.AppendLine(ZString.Format(
                    "・{0}",
                    CreateEffectTextByType(
                        effect.PassEffectType,
                        effect.PassEffectValue)));
            }
            builder.AppendLine();
            
            return builder;
        }

        string CreateEffectTextByType(
            ShopPassEffectType effectType,
            PassEffectValue effectValue)
        {
            return effectType switch
            {
                ShopPassEffectType.IdleIncentiveMaxQuickReceiveByAd => ZString.Format(
                    "広告視聴での探索報酬クイック受け取り回数{0}回増加",
                    effectValue.ToString()),
                ShopPassEffectType.IdleIncentiveMaxQuickReceiveByDiamond => ZString.Format(
                    "プリズムでの探索報酬クイック受け取り回数{0}回増加",
                    effectValue.ToString()),
                ShopPassEffectType.IdleIncentiveAddReward => ZString.Format(
                    "探索報酬{0}倍UP",
                    effectValue.ToString()),
                ShopPassEffectType.StaminaAddRecoveryLimit => ZString.Format(
                    "スタミナ自然回復上限+{0}",
                    effectValue.ToString()),
                ShopPassEffectType.AdSkip => "広告なしで報酬獲得",
                ShopPassEffectType.ChangeBattleSpeed => "バトル速度3倍速開放",
                _ => string.Empty
            };
        }
    }
}
