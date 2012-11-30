<?php

function getDraftStatusGroup()
{
	return array(MORE_WORK_REQUIRED, DRAFT, SUBMITTED_FOR_ASSESSMENT, ASSESSMENT_IN_PROGRESS);
}

function getApprovedStatusGroup()
{
	return array(APPROVED, PUBLISHED);
}