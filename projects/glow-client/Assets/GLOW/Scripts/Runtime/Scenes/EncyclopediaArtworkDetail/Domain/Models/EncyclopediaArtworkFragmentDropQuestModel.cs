using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models
{
    public record EncyclopediaArtworkFragmentDropQuestModel(MasterDataId MstEventId, MasterDataId GroupId, Difficulty Difficulty);
}
