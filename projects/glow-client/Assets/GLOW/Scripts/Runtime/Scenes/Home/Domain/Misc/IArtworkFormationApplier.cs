using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Misc
{
    public interface IArtworkFormationApplier
    {
        void AsyncApplyArtworkFormation(IReadOnlyList<MasterDataId> mstArtworkIds);
    }
}

