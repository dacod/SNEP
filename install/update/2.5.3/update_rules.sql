UPDATE regras_negocio_actions SET action='PBX_Rule_Action_ActionLoop' WHERE action='Snep_Action_ActionLoop';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_CCustos' WHERE action='Snep_Action_CCustos';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_Cadeado' WHERE action='Snep_Action_Cadeado';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_DiscarRamal' WHERE action='Snep_Action_DiscarRamal';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_DiscarTronco' WHERE action='Snep_Action_DiscarTronco';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_GoContext' WHERE action='Snep_Action_GoContext';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_Queue' WHERE action='Snep_Action_Queue';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_Restore' WHERE action='Snep_Action_Restore';
UPDATE regras_negocio_actions SET action='PBX_Rule_Action_Rewrite' WHERE action='Snep_Action_Rewrite';

ALTER TABLE trunks ADD map_extensions BOOLEAN DEFAULT FALSE;
ALTER TABLE trunks ADD reverse_auth BOOLEAN DEFAULT TRUE;