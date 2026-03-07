using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;
using UnityEngine;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public static class DebugArtworkEffectTypeExtension
    {
        public static string GetEffectTypeText(ArtworkEffectType type)
        {
            return type switch
            {
                ArtworkEffectType.AttackPowerUp => "攻撃力up",
                ArtworkEffectType.HpUp => "体力up",
                ArtworkEffectType.ResummonSpeedUp => "再召喚時間短縮",
                ArtworkEffectType.SpecialAttackChargeSpeedUp => "必殺ワザ再発動時間",
                ArtworkEffectType.InitialLeaderPointUp => "バトル開始時所持リーダーP増加",
                ArtworkEffectType.JumbleRushChargeSpeedUp => "JUMBLE RUSH発動までの時間短縮",
                ArtworkEffectType.JumbleRushDamageUp => "JUMBLE RUSHのダメージup",
                _ => string.Empty
            };
        }
    }

    public record DebugArtworkEffectUseCaseModel(
        IReadOnlyList<DebugArtworkEffectElementUseCaseModel> Elements)
    {
        public IReadOnlyList<DebugArtworkEffectElementUseCaseModel> GetSortElementFromMstSeries()
        {
            return Elements
                .OrderBy(e => e.GetSeriesIdString())
                .ToList();
        }
    };

    public record DebugArtworkEffectElementUseCaseModel(
        DebugArtworkEffectElementSummaryUseCaseModel Summary,
        DebugArtworkEffectElementEffectActivationUseCaseModel EffectActivation,
        // IReadOnlyList<DebugArtworkEffectElementEffectCoefUseCaseModel> EffectCoefs,
        DebugArtworkEffectElementEffectTargetUseCaseModel EffectTarget,
        DebugArtworkEffectElementEffectDetailUseCaseModel EffectDetail,
        IReadOnlyList<DebugArtworkEffectElementEffectDescriptionUseCaseModel> EffectDescriptions)
    {
        public string GetNameString()
        {
            // レイアウトのために改行, ホワイトスペース入れる
            return $"{GetSeriesIdString()}: {Summary.MstArtworkId.Value}\n　　{Summary.Name.Value}";
        }

        public string GetSeriesIdString()
        {
            var parts = Summary.MstArtworkId.Value.ToString().Split('_');
            return parts.Length >= 2 ? parts[^2] : string.Empty;
        }
    }

    public record DebugArtworkEffectElementSummaryUseCaseModel(
        MasterDataId MstArtworkId,
        MasterDataId MstSeriesId, //ソート用
        ArtworkName Name,
        Rarity Rarity,
        DebugArtworkEffectElementAcquisionRouteUseCaseModel RouteUseCaseModel, //設計書には「カテゴリ」記載
        ArtworkEffectType EffectType,
        ArtworkEffectTargetValue TargetValue // 対象数
    )
    {
        public string GetCategoryText()
        {
            if (RouteUseCaseModel.IsEmpty())
            {
                // TODO: 既存獲得先が散逸しており表現が難しいので、暫定的に未記載にしている
                return "--";
            }

            return RouteUseCaseModel.GetTypeText();
        }
    };

    public record DebugArtworkEffectElementAcquisionRouteUseCaseModel(ArtworkAcquisitionRouteType? Type)
    {
        public static DebugArtworkEffectElementAcquisionRouteUseCaseModel Empty { get; } =
            new DebugArtworkEffectElementAcquisionRouteUseCaseModel((ArtworkAcquisitionRouteType?)null);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string GetTypeText()
        {
            return Type switch
            {
                ArtworkAcquisitionRouteType.Fragment => "かけら収集",
                ArtworkAcquisitionRouteType.UnitGrade => "ユニットのグレードを5にする",
                ArtworkAcquisitionRouteType.Exchange => "交換所で交換する",
                ArtworkAcquisitionRouteType.PanelMission => "パネルミッション",
                ArtworkAcquisitionRouteType.EventMission => "イベントミッション",
                ArtworkAcquisitionRouteType.BoxGasha => "BOXガシャ",
                ArtworkAcquisitionRouteType.Gasha => "ガシャ",
                _ => string.Empty
            };
        }
    };

    // 発動条件
    public record DebugArtworkEffectElementEffectActivationUseCaseModel(
        DebugArtworkEffectElementEffectActivationElementUseCaseModel EffectActivation);

    public record DebugArtworkEffectElementEffectActivationElementUseCaseModel(
        ArtworkEffectActivationRuleType Type,
        ArtworkEffectActivationValue Value,
        SeriesName TargetSeriesName,
        MasterDataId TargetMstUnitId)
    {
        // 作品条件, 条件対数
        public (string, string) GetSeriesActivation()
        {
            if (Type != ArtworkEffectActivationRuleType.Series)
            {
                return (string.Empty, string.Empty);
            }

            return (TargetSeriesName.Value, Value.ToInt().ToString());
        }

        // 属性条件, 条件対数
        public (string, string) GetColorActivation()
        {
            if (Type != ArtworkEffectActivationRuleType.CharacterColor)
            {
                return (string.Empty, string.Empty);
            }

            return (GetColorActivationText(), string.Empty);
        }

        // ロール条件, 条件対数
        public (string, string) GetRoleActivation()
        {
            if (Type != ArtworkEffectActivationRuleType.CharacterUnitRoleType)
            {
                return (string.Empty, string.Empty);
            }

            return (GetRoleActivationText(), string.Empty);
        }

        string GetColorActivationText()
        {
            if (Type != ArtworkEffectActivationRuleType.CharacterColor)
            {
                return string.Empty;
            }

            return Value.ToCharacterColor().ToString();
        }

        string GetRoleActivationText()
        {
            if (Type != ArtworkEffectActivationRuleType.CharacterUnitRoleType)
            {
                return string.Empty;
            }

            return Value.ToCharacterUnitRoleType().ToString();
        }
    };

    // マスター無影響(未使用)
    // public record DebugArtworkEffectElementEffectCoefUseCaseModel(
    //
    //
    // );

    // 発動対象
    public record DebugArtworkEffectElementEffectTargetUseCaseModel(
        IReadOnlyList<DebugArtworkEffectElementEffectTargetElementUseCaseModel> TargetElements)
    {
        public string GetSeriesTarget()
        {
            var target = TargetElements
                .FirstOrDefault(t => t.Type == ArtworkEffectTargetRuleType.Series);
            if (target == null)
            {
                return string.Empty;
            }

            return target.TargetSeriesName.Value;
        }

        public string GetColorTarget()
        {
            var target = TargetElements
                .FirstOrDefault(t => t.Type == ArtworkEffectTargetRuleType.CharacterColor);
            if (target == null)
            {
                return string.Empty;
            }

            return target.Value.ToCharacterColor().ToString();
        }

        public string GetRoleTarget()
        {
            var target = TargetElements
                .FirstOrDefault(t => t.Type == ArtworkEffectTargetRuleType.CharacterUnitRoleType);
            if (target == null)
            {
                return string.Empty;
            }

            return target.Value.ToCharacterUnitRoleType().ToString();
        }

        public string GetUnitTarget()
        {
            var target = TargetElements
                .FirstOrDefault(t => t.Type == ArtworkEffectTargetRuleType.Unit);
            if (target == null)
            {
                return string.Empty;
            }

            return target.Value.ToMasterDataId().Value.ToString();
        }

        public string GetOtherTarget()
        {
            var target = TargetElements
                .FirstOrDefault(t =>
                    t.Type == ArtworkEffectTargetRuleType.All);
            if (target == null)
            {
                return string.Empty;
            }

            return target.Type == ArtworkEffectTargetRuleType.All ? "全対象" : $"対象数: {target.Value.Value}";
        }
    };

    public record DebugArtworkEffectElementEffectTargetElementUseCaseModel(
        ArtworkEffectTargetRuleType Type,
        ArtworkEffectTargetValue Value,
        SeriesName TargetSeriesName);


    // 技詳細
    public record DebugArtworkEffectElementEffectDetailUseCaseModel(
        ArtworkEffectType? EffectType,
        ArtworkEffectValue Grade1EffectValue,
        ArtworkEffectValue Grade2EffectValue,
        ArtworkEffectValue Grade3EffectValue,
        ArtworkEffectValue Grade4EffectValue,
        ArtworkEffectValue Grade5EffectValue
    )
    {
        public static DebugArtworkEffectElementEffectDetailUseCaseModel Empty { get; } =
            new DebugArtworkEffectElementEffectDetailUseCaseModel(
                (ArtworkEffectType?)null,
                ArtworkEffectValue.Empty,
                ArtworkEffectValue.Empty,
                ArtworkEffectValue.Empty,
                ArtworkEffectValue.Empty,
                ArtworkEffectValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string GetEffectTypeString()
        {
            if (EffectType == null)
            {
                return string.Empty;
            }

            return DebugArtworkEffectTypeExtension.GetEffectTypeText(EffectType.Value);
        }
    };

    // 効果文言
    public record DebugArtworkEffectElementEffectDescriptionUseCaseModel(
        ArtworkGradeLevel GradeLevel,
        ArtworkEffectDescription Description);

    public class DebugArtworkEffectUseCase
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstArtworkEffectRepository MstArtworkEffectRepository { get; } // 技詳細
        [Inject] IMstArtworkEffectDescriptionDataRepository MstArtworkEffectDescriptionDataRepository { get; } // 効果文言
        [Inject] IMstArtworkAcquisitionRouteRepository MstArtworkAcquisitionRouteRepository { get; } // 入手経路
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }

        public DebugArtworkEffectUseCaseModel GetModel()
        {
            var mstArtworkModels = MstArtworkDataRepository.GetArtworks();
            var elements = mstArtworkModels
                .Select(mstArtworkModel =>
                    new DebugArtworkEffectElementUseCaseModel(
                        CreateSummary(mstArtworkModel),
                        CreateEffectActivation(mstArtworkModel),
                        // CreateEffectCoefs(mstArtworkModel),
                        CreateEffectTarget(mstArtworkModel),
                        CreateEffectDetail(mstArtworkModel),
                        CreateEffectDescriptions(mstArtworkModel)
                    )).ToList();

            return new DebugArtworkEffectUseCaseModel(elements);
        }

        DebugArtworkEffectElementSummaryUseCaseModel CreateSummary(MstArtworkModel mstArtworkModel)
        {
            var mstArtworkAcquisitionRouteModel =
                MstArtworkAcquisitionRouteRepository.GetArtworkAcquisitionRouteFirstOrDefault(mstArtworkModel.Id);
            var mstArtworkEffectModel =
                MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(mstArtworkModel.Id);
            var targetValueSum =
                mstArtworkEffectModel.MstArtworkEffectModels.First()
                    .TargetRules.Sum(r => r.Value.ToInt());

            if (mstArtworkAcquisitionRouteModel.IsEmpty())
            {
                return new DebugArtworkEffectElementSummaryUseCaseModel(
                    mstArtworkModel.Id,
                    mstArtworkModel.MstSeriesId,
                    mstArtworkModel.Name,
                    mstArtworkModel.Rarity,
                    DebugArtworkEffectElementAcquisionRouteUseCaseModel.Empty,
                    mstArtworkEffectModel.MstArtworkEffectModels.First().Type, //てきとう。2つ以上効果あったとき未検討
                    new ArtworkEffectTargetValue(targetValueSum.ToString())
                );
            }

            return new DebugArtworkEffectElementSummaryUseCaseModel(
                mstArtworkModel.Id,
                mstArtworkModel.MstSeriesId,
                mstArtworkModel.Name,
                mstArtworkModel.Rarity,
                new DebugArtworkEffectElementAcquisionRouteUseCaseModel(
                    mstArtworkAcquisitionRouteModel.AcquisitionRoutes.First().Type),
                mstArtworkEffectModel.MstArtworkEffectModels.First().Type, //てきとう。2つ以上効果あったとき未検討
                new ArtworkEffectTargetValue(targetValueSum.ToString())
            );
        }

        DebugArtworkEffectElementEffectActivationUseCaseModel CreateEffectActivation(MstArtworkModel mstArtworkModel)
        {
            var mstArtworkEffectModel =
                MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(mstArtworkModel.Id);

            var effectType = mstArtworkEffectModel.MstArtworkEffectModels
                .SelectMany(m => m.ActivationRules)
                .First(m => m.Type != ArtworkEffectActivationRuleType.Count);

            var countValue = mstArtworkEffectModel.MstArtworkEffectModels
                .SelectMany(m => m.ActivationRules)
                .FirstOrDefault(m => m.Type == ArtworkEffectActivationRuleType.Count)?.Value.ToInt() ?? 0;

            var targetSeriesName =
                effectType.Type == ArtworkEffectActivationRuleType.Series
                    ? MstSeriesDataRepository.GetMstSeriesModel(effectType.Value.ToMasterDataId()).Name
                    : SeriesName.Empty;

            var targetMstUnitId = effectType.Type == ArtworkEffectActivationRuleType.Unit
                ? effectType.Value.ToMasterDataId()
                : MasterDataId.Empty;

            var effectActivation = new DebugArtworkEffectElementEffectActivationElementUseCaseModel(
                effectType.Type,
                new ArtworkEffectActivationValue(countValue.ToString()),
                targetSeriesName,
                targetMstUnitId
            );

            return new DebugArtworkEffectElementEffectActivationUseCaseModel(effectActivation);
        }

        DebugArtworkEffectElementEffectTargetUseCaseModel CreateEffectTarget(MstArtworkModel mstArtworkModel)
        {
            var mstArtworkEffectModel =
                MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(mstArtworkModel.Id);

            if (mstArtworkEffectModel.IsEmpty())
            {
                return new DebugArtworkEffectElementEffectTargetUseCaseModel(
                    new List<DebugArtworkEffectElementEffectTargetElementUseCaseModel>());
            }

            var effectTargets = mstArtworkEffectModel.MstArtworkEffectModels
                .SelectMany(x => x.TargetRules)
                .Select(x =>
                {
                    var targetSeriesName =
                        x.Type == ArtworkEffectTargetRuleType.Series
                            ? MstSeriesDataRepository.GetMstSeriesModel(x.Value.ToMasterDataId()).Name
                            : SeriesName.Empty;
                    return new DebugArtworkEffectElementEffectTargetElementUseCaseModel(x.Type, x.Value, targetSeriesName);
                })
                .ToList();

            return new DebugArtworkEffectElementEffectTargetUseCaseModel(effectTargets);
        }

        DebugArtworkEffectElementEffectDetailUseCaseModel CreateEffectDetail(MstArtworkModel mstArtworkModel)
        {
            var mstArtworkEffectModel =
                MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(mstArtworkModel.Id);
            if (mstArtworkEffectModel.IsEmpty())
            {
                return DebugArtworkEffectElementEffectDetailUseCaseModel.Empty;
            }

            var effectModel = mstArtworkEffectModel.MstArtworkEffectModels.First(); //てきとう。2つ以上効果あったとき未検討

            return new DebugArtworkEffectElementEffectDetailUseCaseModel(
                effectModel.Type,
                effectModel.Grade1Value,
                effectModel.Grade2Value,
                effectModel.Grade3Value,
                effectModel.Grade4Value,
                effectModel.Grade5Value);
        }

        IReadOnlyList<DebugArtworkEffectElementEffectDescriptionUseCaseModel>
            CreateEffectDescriptions(MstArtworkModel mstArtworkModel)
        {
            var mstArtworkEffectDescriptionModels =
                MstArtworkEffectDescriptionDataRepository.GetArtworkEffectDescriptionFirstOrDefault(mstArtworkModel.Id);

            if (mstArtworkEffectDescriptionModels.IsEmpty())
            {
                return new List<DebugArtworkEffectElementEffectDescriptionUseCaseModel>();
            }

            return mstArtworkEffectDescriptionModels.Descriptions
                .Select(x => new DebugArtworkEffectElementEffectDescriptionUseCaseModel(x.GradeLevel, x.Description))
                .ToList();
        }
    }
}
