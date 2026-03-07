using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public class ArtworkEffectModelFactory : IArtworkEffectModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkEffectRepository MstArtworkEffectRepository { get; }

        public ArtworkEffectModel Create()
        {
            List<ArtworkEffectElement> effectElements = new();

            // 原画パーティ情報
            var userArtworkPartyModel = GameRepository.GetGameFetchOther().UserArtworkPartyModel;
            // 原画情報(グレードの取得)
            var userArtworkModels = GameRepository.GetGameFetchOther().UserArtworkModels;
            foreach (var userArtwork in userArtworkPartyModel.GetArtworkList())
            {
                if (userArtwork.IsEmpty()) continue;

                var mstArtworkEffectModel = MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(userArtwork);
                var userArtworkModel = userArtworkModels.FirstOrDefault(model => model.MstArtworkId == userArtwork, UserArtworkModel.Empty);
                var artworkEffectElements = ToArtworkEffectElementList(mstArtworkEffectModel, userArtworkModel.Grade);

                effectElements.AddRange(artworkEffectElements);
            }

            return new ArtworkEffectModel(effectElements);
        }

        public ArtworkEffectModel CreatePvpOpponent(IReadOnlyList<ArtworkPartyStatusModel> pvpOpponentArtworkPartyStatuses)
        {
            List<ArtworkEffectElement> effectElements = new();
            foreach (var partyStatus in pvpOpponentArtworkPartyStatuses)
            {
                if (partyStatus.IsEmpty()) continue;

                var mstArtworkEffectModel = MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(partyStatus.MstArtworkId);
                var artworkEffectElements = ToArtworkEffectElementList(
                    mstArtworkEffectModel,
                    partyStatus.ArtworkGradeLevel);

                effectElements.AddRange(artworkEffectElements);
            }

            return new ArtworkEffectModel(effectElements);
        }

        IReadOnlyList<ArtworkEffectElement> ToArtworkEffectElementList(
            MstInGameArtworkEffectModel mstArtworkEffectModel,
            ArtworkGradeLevel artworkGrade)
        {
            var effectElements = mstArtworkEffectModel.MstArtworkEffectModels
                .Select(effectModel => ToArtworkEffectElement(effectModel, artworkGrade))
                .ToList();

            return effectElements;
        }

        ArtworkEffectElement ToArtworkEffectElement(
            MstArtworkEffectModel mstArtworkEffectModel,
            ArtworkGradeLevel artworkGrade)
        {
            return new ArtworkEffectElement(
                mstArtworkEffectModel.Type,
                mstArtworkEffectModel.GetGradeValue(artworkGrade),
                mstArtworkEffectModel.TargetRules
                    .Select(ToArtworkEffectTargetRuleModel)
                    .ToList(),
                mstArtworkEffectModel.ActivationRules
                    .Select(ToArtworkEffectActivationRuleModel)
                    .ToList());
        }

        ArtworkEffectTargetRuleModel ToArtworkEffectTargetRuleModel(MstArtworkEffectTargetRuleModel targetRuleModel)
        {
            return new ArtworkEffectTargetRuleModel(
                targetRuleModel.Type,
                targetRuleModel.Value);
        }

        ArtworkEffectActivationRuleModel ToArtworkEffectActivationRuleModel(
            MstArtworkEffectActivationRuleModel activationRuleModel)
        {
            return new ArtworkEffectActivationRuleModel(
                activationRuleModel.Type,
                activationRuleModel.Value);
        }
    }
}
